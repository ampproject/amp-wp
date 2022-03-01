## Method `AMP_Base_Sanitizer::has_light_shadow_dom()`

```php
public function has_light_shadow_dom();
```

Determine whether the resulting AMP element uses a &quot;light&quot; shadow DOM.

Sometimes AMP components serve as wrappers for native elements, like `amp-img` for `img`. When this is the case, authors sometimes will want to style the shadow element (such as to set object-fit). Normally if a selector contains `img` then the style sanitizer will always convert this to `amp-img` (and `amp-anim`), which may break the author&#039;s intended selector target. So when using a sanitizer&#039;s selector conversion mapping to rewrite non-AMP to AMP selectors, it will first check to see if the selector already mentions an AMP tag and if so it will skip the conversions for that selector. In this way, an `amp-img img` selector will not get converted into `amp-img amp-img`. The selector mapping also is involved when doing tree shaking. In the case of the selector `amp-img img`, the tree shaker would normally strip out this selector because no `img` may be present in the page as it is added by the AMP runtime (unless noscript fallbacks have been added, and this also disregards data-hero images which are added later by AMP Optimizer). So in order to prevent such selectors from being stripped out, it&#039;s important to include the `amp-img` selector among the `dynamic_element_selectors` so that the `img` in the `amp-img img` selector is ignored for the purposes of tree shaking. This method is used to indicate which sanitizers are involved in such element conversions. If this method returns true, then the keys in the selector conversion mapping should be used as `dynamic_element_selectors`.
 In other words, this method indicates whether keys in the conversion mapping are ancestors of elements which are created at runtime. This method is only relevant when the `get_selector_conversion_mapping()` method returns a mapping.

### Return value

`bool` - Whether light DOM is used.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:168](/includes/sanitizers/class-amp-base-sanitizer.php#L168-L170)

<details>
<summary>Show Code</summary>

```php
public function has_light_shadow_dom() {
	return true;
}
```

</details>
