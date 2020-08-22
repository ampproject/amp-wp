## Class `AMP_Twitter_Embed_Handler`

Class AMP_Twitter_Embed_Handler

Much of this class is borrowed from Jetpack embeds

### Methods
<details>
<summary>`register_embed`</summary>

```php
public register_embed()
```

Registers embed.


</details>
<details>
<summary>`unregister_embed`</summary>

```php
public unregister_embed()
```

Unregisters embed.


</details>
<details>
<summary>`oembed_timeline`</summary>

```php
public oembed_timeline( $matches )
```

Render oEmbed for a timeline.


</details>
<details>
<summary>`sanitize_raw_embeds`</summary>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitized &lt;blockquote class=&quot;twitter-tweet&quot;&gt; tags to &lt;amp-twitter&gt;.


</details>
<details>
<summary>`is_tweet_raw_embed`</summary>

```php
private is_tweet_raw_embed( $node )
```

Checks whether it&#039;s a twitter blockquote or not.


</details>
<details>
<summary>`create_amp_twitter_and_replace_node`</summary>

```php
private create_amp_twitter_and_replace_node( Document $dom, \DOMElement $node )
```

Make final modifications to DOMNode


</details>
<details>
<summary>`get_tweet_id`</summary>

```php
private get_tweet_id( $node )
```

Extracts Tweet id.


</details>
<details>
<summary>`sanitize_embed_script`</summary>

```php
private sanitize_embed_script( $node )
```

Removes Twitter&#039;s embed &lt;script&gt; tag.


</details>
