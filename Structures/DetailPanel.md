```json
{
    ":tag": "DetailPanel",
    ":children": [
        {
            ":tag": "FormGroup",
            "type": "text",
            "name": "firstname",
            "title": "First Name",
            ":rules": ["required", "min:10"]
        },
        {
            ":tag": "FormGroup",
            "type": "text",
            "name": "lastname",
            "title": "Last Name",
            ":rules": ["required", "max:10"]
        },
        {
            ":tag": "FormGroup",
            "type": "email",
            "name": "email",
            "title": "Email",
            ":rules": ["required"]
        }
    ]
}
```
