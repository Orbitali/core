<?php

namespace Orbitali\Foundations\Html;

use BadMethodCallException;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Orbitali\Foundations\Html\Exceptions\MissingTag;
use Orbitali\Foundations\Html\Exceptions\InvalidHtml;
use Illuminate\Support\Traits\Macroable;
use Orbitali\Foundations\Html\Exceptions\InvalidChild;
use Illuminate\Contracts\Support\Htmlable;

abstract class BaseElement implements Htmlable, HtmlElement
{
    use Macroable {
        __call as __macro_call;
    }

    /** @var string */
    protected $tag;

    /** @var \Orbitali\Foundations\Html\Attributes */
    protected $attributes;

    /** @var \Illuminate\Support\Collection */
    protected $children;

    public function __construct()
    {
        if (empty($this->tag)) {
            throw MissingTag::onClass(static::class);
        }

        $this->attributes = new Attributes();
        $this->children = new Collection();
    }

    public static function create()
    {
        return new static();
    }

    /**
     * @param string $attribute
     * @param string|null $value
     *
     * @return static
     */
    public function attribute($attribute, $value = null)
    {
        $element = clone $this;

        $element->attributes->setAttribute($attribute, (string) $value);

        return $element;
    }

    /**
     * @param iterable $attributes
     *
     * @return static
     */
    public function attributes($attributes)
    {
        $element = clone $this;

        $element->attributes->setAttributes($attributes);

        return $element;
    }

    /**
     * @param string $attribute
     *
     * @return static
     */
    public function forgetAttribute($attribute)
    {
        $element = clone $this;

        $element->attributes->forgetAttribute($attribute);

        return $element;
    }

    /**
     * @param string $attribute
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function getAttribute($attribute, $fallback = null)
    {
        return $this->attributes->getAttribute($attribute, $fallback);
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function hasAttribute($attribute)
    {
        return $this->attributes->hasAttribute($attribute);
    }

    /**
     * @param iterable|string $class
     *
     * @return static
     */
    public function class($class)
    {
        return $this->addClass($class);
    }

    /**
     * Alias for `class`.
     *
     * @param iterable|string $class
     *
     * @return static
     */
    public function addClass($class)
    {
        $element = clone $this;

        $element->attributes->addClass($class);

        return $element;
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public function id($id)
    {
        return $this->attribute("id", $id);
    }

    /**
     * @param array|string|null $style
     *
     * @return static
     */
    public function style($style)
    {
        if (is_array($style)) {
            $style = implode(
                "; ",
                array_map(
                    function ($value, $attribute) {
                        return "{$attribute}: {$value}";
                    },
                    $style,
                    array_keys($style)
                )
            );
        }

        return $this->attribute("style", $style);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return static
     */
    public function data($name, $value)
    {
        return $this->attribute("data-{$name}", $value);
    }

    /**
     * @param \Orbitali\Foundations\Html\HtmlElement|string|iterable|null $children
     * @param callable|null $mapper
     *
     * @return static
     */
    public function addChildren($children, $mapper = null)
    {
        if (is_null($children)) {
            return $this;
        }

        $children = $this->parseChildren($children, $mapper);

        $element = clone $this;

        $element->children = $element->children->merge($children);

        return $element;
    }

    /**
     * Alias for `addChildren`.
     *
     * @param \Orbitali\Foundations\Html\HtmlElement|string|iterable|null $children
     * @param callable|null $mapper
     *
     * @return static
     */
    public function addChild($child, $mapper = null)
    {
        return $this->addChildren($child, $mapper);
    }

    /**
     * Alias for `addChildren`.
     *
     * @param \Orbitali\Foundations\Html\HtmlElement|string|iterable|null $children
     * @param callable|null $mapper
     *
     * @return static
     */
    public function child($child, $mapper = null)
    {
        return $this->addChildren($child, $mapper);
    }

    /**
     * Alias for `addChildren`.
     *
     * @param \Orbitali\Foundations\Html\HtmlElement|string|iterable|null $children
     * @param callable|null $mapper
     *
     * @return static
     */
    public function children($children, $mapper = null)
    {
        return $this->addChildren($children, $mapper);
    }

    /**
     * Replace all children with an array of elements.
     *
     * @param \Orbitali\Foundations\Html\HtmlElement[] $children
     * @param callable|null $mapper
     *
     * @return static
     */
    public function setChildren($children, $mapper = null)
    {
        $element = clone $this;

        $element->children = new Collection();

        return $element->addChildren($children, $mapper);
    }

    /**
     * @param \Orbitali\Foundations\Html\HtmlElement|string|iterable|null $children
     * @param callable|null $mapper
     *
     * @return static
     */
    public function prependChildren($children, $mapper = null)
    {
        $children = $this->parseChildren($children, $mapper);

        $element = clone $this;

        $element->children = $children->merge($element->children);

        return $element;
    }

    /**
     * Alias for `prependChildren`.
     *
     * @param \Orbitali\Foundations\Html\HtmlElement|string|iterable|null $children
     * @param callable|null $mapper
     *
     * @return static
     */
    public function prependChild($children, $mapper = null)
    {
        return $this->prependChildren($children, $mapper);
    }

    /**
     * @param string|null $text
     *
     * @return static
     */
    public function text($text)
    {
        return $this->html(htmlentities($text, ENT_QUOTES, "UTF-8", false));
    }

    /**
     * @param string|null $html
     *
     * @return static
     */
    public function html($html)
    {
        if ($this->isVoidElement()) {
            throw new InvalidHtml(
                "Can't set inner contents on `{$this->tag}` because it's a void element"
            );
        }

        return $this->setChildren($html);
    }

    /**
     * Conditionally transform the element. Note that since elements are
     * immutable, you'll need to return a new instance from the callback.
     *
     * @param bool $condition
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function if(bool $condition, \Closure $callback)
    {
        return $condition ? $callback($this) : $this;
    }

    /**
     * Conditionally transform the element. Note that since elements are
     * immutable, you'll need to return a new instance from the callback.
     *
     * @param bool $condition
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function unless(bool $condition, \Closure $callback)
    {
        return $this->if(!$condition, $callback);
    }

    /**
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function open()
    {
        $tagInternal = $this->attributes->isEmpty()
            ? "<" . $this->tag . ">"
            : "<{$this->tag} {$this->attributes->render()}>";

        $childrenInternal = $this->children
            ->map(function ($child): string {
                if ($child instanceof HtmlElement) {
                    return $child->render();
                }

                if (is_null($child)) {
                    return "";
                }

                if (is_string($child)) {
                    return $child;
                }

                throw InvalidChild::childMustBeAnHtmlElementOrAString();
            })
            ->implode("");

        return new HtmlString($tagInternal . $childrenInternal);
    }

    /**
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function close()
    {
        return new HtmlString($this->isVoidElement() ? "" : "</{$this->tag}>");
    }

    /**
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function render()
    {
        if (html()->readonly) {
            $this->attributes->setAttribute("readonly", "true");
        }
        return new HtmlString($this->open() . $this->close());
    }

    public function isVoidElement(): bool
    {
        return in_array($this->tag, [
            "area",
            "base",
            "br",
            "col",
            "embed",
            "hr",
            "img",
            "input",
            "keygen",
            "link",
            "menuitem",
            "meta",
            "param",
            "source",
            "track",
            "wbr",
        ]);
    }

    /**
     * Dynamically handle calls to the class.
     * Check for methods finishing by If or fallback to Macroable.
     *
     * @param  string  $name
     * @param  array   $arguments
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        if (
            \Orbitali\Foundations\Helpers\Arr::endWiths(
                $name,
                $conditions = ["If", "Unless"]
            )
        ) {
            foreach ($conditions as $condition) {
                if (
                    !method_exists(
                        $this,
                        $method = str_replace($condition, "", $name)
                    )
                ) {
                    continue;
                }

                return $this->callConditionalMethod(
                    $condition,
                    $method,
                    $arguments
                );
            }
        }

        return $this->__macro_call($name, $arguments);
    }

    protected function callConditionalMethod($type, $method, array $arguments)
    {
        $condition = (bool) array_shift($arguments);
        $callback = function () use ($method, $arguments) {
            return $this->{$method}(...$arguments);
        };

        switch ($type) {
            case "If":
                return $this->if($condition, $callback);
            case "Unless":
                return $this->unless($condition, $callback);
            default:
                return $this;
        }
    }

    public function __clone()
    {
        $this->attributes = clone $this->attributes;
        $this->children = clone $this->children;
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    protected function parseChildren($children, $mapper = null): Collection
    {
        if ($children instanceof HtmlElement) {
            $children = [$children];
        }

        $children = Collection::make($children);

        if ($mapper) {
            $children = $children->map($mapper);
        }
        $this->guardAgainstInvalidChildren($children);

        return $children;
    }

    protected function guardAgainstInvalidChildren(Collection $children)
    {
        foreach ($children as $child) {
            if (
                !$child instanceof HtmlElement &&
                !is_string($child) &&
                !is_null($child)
            ) {
                throw InvalidChild::childMustBeAnHtmlElementOrAString();
            }
        }
    }
}
