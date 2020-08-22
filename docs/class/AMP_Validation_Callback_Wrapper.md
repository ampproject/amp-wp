## Class `AMP_Validation_Callback_Wrapper`

Class AMP_Validation_Callback_Wrapper

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $callback )
```

AMP_Validation_Callback_Wrapper constructor.


</details>
<details>
<summary>`prepare`</summary>

```php
protected prepare( $args )
```

Prepare for invocation.


</details>
<details>
<summary>`__invoke`</summary>

```php
public __invoke( $args )
```

Invoke wrapped callback.


</details>
<details>
<summary>`invoke_with_first_ref_arg`</summary>

```php
public invoke_with_first_ref_arg( $first_arg, $other_args )
```

Invoke wrapped callback with first argument passed by reference.


</details>
<details>
<summary>`finalize`</summary>

```php
protected finalize( array $preparation )
```

Finalize invocation.


</details>
<details>
<summary>`finalize_styles`</summary>

```php
protected finalize_styles( \WP_Styles $wp_styles, array $before_registered, array $before_enqueued, array $before_extras )
```

Finalize styles after invocation.


</details>
<details>
<summary>`finalize_scripts`</summary>

```php
protected finalize_scripts( \WP_Scripts $wp_scripts, array $before_registered, array $before_enqueued, array $before_extras )
```

Finalize scripts after invocation.


</details>
<details>
<summary>`offsetSet`</summary>

```php
public offsetSet( $offset, $value )
```

Offset set.


</details>
<details>
<summary>`offsetExists`</summary>

```php
public offsetExists( $offset )
```

Offset exists.


</details>
<details>
<summary>`offsetUnset`</summary>

```php
public offsetUnset( $offset )
```

Offset unset.


</details>
<details>
<summary>`offsetGet`</summary>

```php
public offsetGet( $offset )
```

Offset get.


</details>
