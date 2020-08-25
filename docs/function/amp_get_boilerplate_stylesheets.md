## Function `amp_get_boilerplate_stylesheets`

```php
function amp_get_boilerplate_stylesheets();
```

Get AMP boilerplate stylesheets.

### Return value

`string[]` - Stylesheets, where first is contained in style[amp-boilerplate] and the second in noscript&gt;style[amp-boilerplate].

### Source

:link: [includes/amp-helper-functions.php:943](../../includes/amp-helper-functions.php#L943-L948)

<details>
<summary>Show Code</summary>

```php
function amp_get_boilerplate_stylesheets() {
	return [
		'body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}',
		'body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}',
	];
}
```

</details>
