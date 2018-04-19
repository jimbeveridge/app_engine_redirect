## Simple PHP script for redirects in App Engine

Maintainer: jimbe@google.com

## Configure

Edit redirect.php and modify the `$redirects` map as desired.

## Deploy

```
$ gcloud app deploy --project saline-nachos-27325
```

Note that app.yaml configures this as the "redirect" service, so the domain
name for this project will be prefixed by that word.

## Notes

The use of PHP was just expedient.
