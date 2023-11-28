Routes:

```yaml
integrated_woodwing:
    resource: "@IntegratedWoodwingBundle/Resources/config/routing.xml"
    prefix: "/api/woodwing/"

```

Env:
``` 
WOODWING_API_SECRET={secret}
```

Make sure to add content types for:
- Woodwing posts
- Editions
- Layouts

The latter should be linked to the woodwing channels.
