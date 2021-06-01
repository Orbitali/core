<?php

namespace Orbitali\Components;

use Illuminate\View\Component;
use Illuminate\Container\Container;

class StructureComponent extends Component
{
    private $structure;
    private $factory;
    private $directory;
    private $viewFile;
    private $viewAlias;
    /**
     * Create a new component instance.
     *
     * @param  Model  $structure
     * @return void
     */
    public function __construct($structure)
    {
        $this->structure = $structure;
        $this->factory = Container::getInstance()->make("view");
        $this->directory = Container::getInstance()["config"]->get(
            "view.compiled"
        );
        $this->factory->addNamespace("__components", $this->directory);

        $content = $this->structure->getRawOriginal("data");
        $this->viewFile = "$this->directory/" . sha1($content) . ".blade.php";
        $this->viewAlias =
            "__components::" . basename($this->viewFile, ".blade.php");
    }

    /**
     * Get the view / view contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\Support\Htmlable|\Closure|string
     */
    public function render()
    {
        return <<<'blade'
<x-orbitali::detail-panel id="dp1">
    <x-orbitali::text-input id="ti1" name="name" title="Name" :parent="$component" required />
</x-orbitali::detail-panel>

<x-orbitali::tab-container id="tc1">
    <x-orbitali::tab-panel id="tp1" title="Test Title" :parent="$component">
        <x-orbitali::text-input id="r1" name="domain" title="Domain" :parent="$component" required />
    </x-orbitali::tab-panel>
</x-orbitali::tab-container>
blade;
    }

    /**
     * Resolve the Blade view or view file that should be used when rendering the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\Support\Htmlable|\Closure|string
     */
    public function resolveView()
    {
        if ($this->factory->exists($this->viewAlias)) {
            return $this->viewAlias;
        }
        return $this->createBladeViewFromString(null, $this->render());
    }

    /**
     * Create a Blade view with the raw component string content.
     *
     * @param  string  $contents
     * @return string
     */
    protected function createBladeViewFromString($factory, $contents)
    {
        if (!is_file($this->viewFile)) {
            if (!is_dir($this->directory)) {
                mkdir($this->directory, 0755, true);
            }
            file_put_contents($this->viewFile, $contents);
        }

        return "__components::" . basename($this->viewFile, ".blade.php");
    }
}
