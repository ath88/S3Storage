# FreeScout S3 Storage Module (Official)

Installation instructions are available [here](https://github.com/freescout-help-desk/freescout/wiki/FreeScout-Modules#3-installing-custom-modules).

Full documentation: https://freescout.net/module/s3-storage/

Bug reports and pull requests related to this module should be submitted to this repository.


## Configuring

Add the following parameters to the .env file and clear cache:

```
FILESYSTEM_DRIVER=s3

AWS_ACCESS_KEY_ID=access_key
AWS_SECRET_ACCESS_KEY=secret_key
AWS_DEFAULT_REGION=region_name
AWS_BUCKET=my-bucket
AWS_ENDPOINT=https://endpoint.s3-example.com
# By default S3 driver uses virtual-hosted style for file URLs.
# This parameter enables path-style for file URLs:
# - true: https://s3-example.com/my-bucket/file.jpg
# - false: https://my-bucket.s3-example.com/file.jpg
AWS_USE_PATH_STYLE_ENDPOINT=true
# URLs used to build publicly available file URLs.
AWS_URL=https://s3-example.com
```

When viewing or downloading attachments stored in S3, they are proxied through PHP. Users' photos, customers' photos, and files in '/uploads' are served via direct URLs to S3 storage.

## Contributing

- Fork [master](https://github.com/freescout-help-desk/S3Storage/tree/master) branch.
- Submit Pull Requests into `master` branch.