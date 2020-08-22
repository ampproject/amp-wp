## Class `AMP_Twitter_Embed_Handler`

Class AMP_Twitter_Embed_Handler

Much of this class is borrowed from Jetpack embeds

### Methods
<details>
<summary><code>register_embed</code></summary>

```php
public register_embed()
```

Registers embed.


</details>
<details>
<summary><code>unregister_embed</code></summary>

```php
public unregister_embed()
```

Unregisters embed.


</details>
<details>
<summary><code>oembed_timeline</code></summary>

```php
public oembed_timeline( $matches )
```

Render oEmbed for a timeline.


</details>
<details>
<summary><code>sanitize_raw_embeds</code></summary>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitized &lt;blockquote class=&quot;twitter-tweet&quot;&gt; tags to &lt;amp-twitter&gt;.


</details>
<details>
<summary><code>is_tweet_raw_embed</code></summary>

```php
private is_tweet_raw_embed( $node )
```

Checks whether it&#039;s a twitter blockquote or not.


</details>
<details>
<summary><code>create_amp_twitter_and_replace_node</code></summary>

```php
private create_amp_twitter_and_replace_node( Document $dom, \DOMElement $node )
```

Make final modifications to DOMNode


</details>
<details>
<summary><code>get_tweet_id</code></summary>

```php
private get_tweet_id( $node )
```

Extracts Tweet id.


</details>
<details>
<summary><code>sanitize_embed_script</code></summary>

```php
private sanitize_embed_script( $node )
```

Removes Twitter&#039;s embed &lt;script&gt; tag.


</details>
