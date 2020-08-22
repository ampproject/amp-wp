## Class `AMP_Twitter_Embed_Handler`

Class AMP_Twitter_Embed_Handler

Much of this class is borrowed from Jetpack embeds

### Methods
* `register_embed`

<details>

```php
public register_embed()
```

Registers embed.


</details>
* `unregister_embed`

<details>

```php
public unregister_embed()
```

Unregisters embed.


</details>
* `oembed_timeline`

<details>

```php
public oembed_timeline( $matches )
```

Render oEmbed for a timeline.


</details>
* `sanitize_raw_embeds`

<details>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitized &lt;blockquote class=&quot;twitter-tweet&quot;&gt; tags to &lt;amp-twitter&gt;.


</details>
* `is_tweet_raw_embed`

<details>

```php
private is_tweet_raw_embed( $node )
```

Checks whether it&#039;s a twitter blockquote or not.


</details>
* `create_amp_twitter_and_replace_node`

<details>

```php
private create_amp_twitter_and_replace_node( Document $dom, \DOMElement $node )
```

Make final modifications to DOMNode


</details>
* `get_tweet_id`

<details>

```php
private get_tweet_id( $node )
```

Extracts Tweet id.


</details>
* `sanitize_embed_script`

<details>

```php
private sanitize_embed_script( $node )
```

Removes Twitter&#039;s embed &lt;script&gt; tag.


</details>
