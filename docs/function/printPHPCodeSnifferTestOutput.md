## Function `printPHPCodeSnifferTestOutput`

```php
function printPHPCodeSnifferTestOutput();
```

A global util function to help print unit test fixing data.

### Return value

`void`

### Source

:link: [lib/common/vendor/squizlabs/php_codesniffer/tests/bootstrap.php:53](/lib/common/vendor/squizlabs/php_codesniffer/tests/bootstrap.php#L53-L84)

<details>
<summary>Show Code</summary>

```php
function printPHPCodeSnifferTestOutput()
{
    echo PHP_EOL.PHP_EOL;

    $output = 'The test files';
    $data   = [];

    $codeCount = count($GLOBALS['PHP_CODESNIFFER_SNIFF_CODES']);
    if (empty($GLOBALS['PHP_CODESNIFFER_SNIFF_CASE_FILES']) === false) {
        $files     = call_user_func_array('array_merge', $GLOBALS['PHP_CODESNIFFER_SNIFF_CASE_FILES']);
        $files     = array_unique($files);
        $fileCount = count($files);

        $output = '%d sniff test files';
        $data[] = $fileCount;
    }

    $output .= ' generated %d unique error codes';
    $data[]  = $codeCount;

    if ($codeCount > 0) {
        $fixes   = count($GLOBALS['PHP_CODESNIFFER_FIXABLE_CODES']);
        $percent = round(($fixes / $codeCount * 100), 2);

        $output .= '; %d were fixable (%d%%)';
        $data[]  = $fixes;
        $data[]  = $percent;
    }

    vprintf($output, $data);

}//end printPHPCodeSnifferTestOutput()
```

</details>
