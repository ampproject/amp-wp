## Function `includeFile`

```php
function includeFile( $file );
```

Scope isolated include.

Prevents access to $this/self from included files.

### Arguments

* `mixed $file`

### Source

:link: [lib/optimizer/vendor/composer/ClassLoader.php:442](/lib/optimizer/vendor/composer/ClassLoader.php#L442-L445)

<details>
<summary>Show Code</summary>

```php
function includeFile($file)
{
    include $file;
}
```

</details>
