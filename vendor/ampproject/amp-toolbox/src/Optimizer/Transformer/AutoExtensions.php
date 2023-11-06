<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Html\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Dom\NodeWalker;
use AmpProject\Extension;
use AmpProject\Optimizer\Configuration\AutoExtensionsConfiguration;
use AmpProject\Optimizer\Error\CannotParseJsonData;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\Html\Tag;
use AmpProject\Validator\Spec;
use AmpProject\Validator\Spec\AttributeList\GlobalAttrs;
use AmpProject\Validator\Spec\SpecRule;
use Exception;
use InvalidArgumentException;

/**
 * Auto import all the missing AMP extensions.
 *
 * @package ampproject/amp-toolbox
 */
final class AutoExtensions implements Transformer
{
    /**
     * Some AMP components don't bring their own tag, but enable new attributes on other elements. Most are included in
     * the AMP validation rules, but some are not. These need to be defined manually here.
     *
     * @var array
     */
    const MANUAL_ATTRIBUTE_TO_EXTENSION_MAPPING = [
        Attribute::LIGHTBOX => Extension::LIGHTBOX_GALLERY,
    ];

    /**
     * Attribute prefix for attributes like bindtext, bindaria-labelledby.
     *
     * @var string
     */
    const BIND_SHORT_FORM_PREFIX = 'bind';

    /**
     * Configuration store to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * Validator spec instance.
     *
     * @var Spec
     */
    private $spec;

    /**
     * List of extensions added by the maybeAddExtension method.
     *
     * @var array
     */
    private $addedExtensions = [];

    /**
     * List of extensions that should not be removed even when there is no usage in the HTML.
     *
     * The array key is the extension to protect, the array value is an array of extensions that
     * make up the requirements for protecting the extension. An empty array means the extension
     * is protected unconditionally.
     *
     * @var array
     */
    private $protectedExtensions = [
        'amp-carousel' => ['amp-lightbox-gallery'],
    ];

    /**
     * Instantiate an AutoExtensions object.
     *
     * @param TransformerConfiguration $configuration Configuration store to use.
     * @param Spec                     $spec          Validator spec instance.
     */
    public function __construct(TransformerConfiguration $configuration, Spec $spec)
    {
        $this->configuration = $configuration;
        $this->spec          = $spec;
    }

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        $extensionScripts = $this->extractExtensionScripts($document, $errors);

        if ($this->configuration->get(AutoExtensionsConfiguration::AUTO_EXTENSION_IMPORT)) {
            $extensionScripts = $this->addMissingExtensions($document, $extensionScripts);
        }

        $extensionScripts = $this->removeUnneededExtensions($extensionScripts);

        $this->renderExtensionScripts($document, $extensionScripts);
    }

    /**
     * Extract the extension scripts already present in the document.
     *
     * @param Document        $document Document to extract the extension scripts from.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return Element[] Array of extension scripts.
     */
    private function extractExtensionScripts(Document $document, ErrorCollection $errors)
    {
        $extensionScripts = [];

        // We memorize nodes to be removed first and only remove them after the loop to not mess up the loop index.
        $nodesToRemove = [];

        foreach ($document->head->getElementsByTagName(Tag::SCRIPT) as $script) {
            /** @var Element $script */
            if ($script->getAttribute(Attribute::ID) === Extension::ACCESS) {
                // Explicitly detect amp-access via the script tag in the header to be able to handle amp-access
                // extensions.
                $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, Extension::ACCESS);
                $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, Extension::ANALYTICS);

                $jsonData  = $this->getJsonData($script, $errors);
                $providers = [];

                // Access providers could be single or multiple.
                if (isset($jsonData['vendor'])) {
                    $providers = [$jsonData];
                } elseif (isset($jsonData[0]) && is_array($jsonData[0])) {
                    $providers = $jsonData;
                }

                foreach ($providers as $provider) {
                    $requiredExtension = null;

                    if ($provider['vendor'] === 'laterpay') {
                        $requiredExtension = Extension::ACCESS_LATERPAY;
                    } elseif (
                        $provider['vendor'] === 'scroll'
                        && isset($provider['namespace'])
                        && $provider['namespace'] === 'scroll'
                    ) {
                        $requiredExtension = Extension::ACCESS_SCROLL;
                    }

                    if ($requiredExtension) {
                        $extensionScripts = $this->maybeAddExtension(
                            $document,
                            $extensionScripts,
                            $requiredExtension
                        );
                    }
                }
            } elseif ($script->getAttribute(Attribute::ID) === Extension::SUBSCRIPTIONS) {
                // Explicitly detect amp-subscriptions via the script tag in the header to be able to handle
                // amp-subscriptions extensions.
                $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, Extension::SUBSCRIPTIONS);
                $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, Extension::ANALYTICS);

                $jsonData = $this->getJsonData($script, $errors);
                if (!array_key_exists('services', $jsonData)) {
                    continue;
                }

                foreach ($jsonData['services'] as $service) {
                    if (
                        array_key_exists('serviceId', $service)
                        && $service['serviceId'] === 'subscribe.google.com'
                    ) {
                        $extensionScripts = $this->maybeAddExtension(
                            $document,
                            $extensionScripts,
                            Extension::SUBSCRIPTIONS_GOOGLE
                        );
                        break;
                    }
                }
            }

            $src = $script->getAttribute(Attribute::SRC);

            if (
                ! $script->hasAttribute(Attribute::SRC)
                ||
                Amp::CACHE_ROOT_URL !== substr($src, 0, strlen(Amp::CACHE_ROOT_URL))
            ) {
                continue;
            }

            if (Amp::isRuntimeScript($script)) {
                $extensionScripts[Amp::RUNTIME] = $script;
            } elseif ($script->hasAttribute(Attribute::CUSTOM_ELEMENT)) {
                $extensionScripts[$script->getAttribute(Attribute::CUSTOM_ELEMENT)] = $script;
            } elseif ($script->hasAttribute(Attribute::CUSTOM_TEMPLATE)) {
                $extensionScripts[$script->getAttribute(Attribute::CUSTOM_TEMPLATE)] = $script;
            } else {
                continue;
            }

            $nodesToRemove[] = $script;
        }

        foreach ($nodesToRemove as $nodeToRemove) {
            // Remove the identified extension scripts from the DOM Document so that we can move them.
            $nodeToRemove->parentNode->removeChild($nodeToRemove);
        }

        return $extensionScripts;
    }

    /**
     * Add missing extensions to the array of extension scripts.
     *
     * @param Document  $document         Document to scan for missing extensions.
     * @param Element[] $extensionScripts Array of preexisting extension scripts.
     * @return Element[] Adapted array of extension scripts that includes the previously missing ones.
     */
    private function addMissingExtensions(Document $document, $extensionScripts)
    {
        $node = $document->body;

        while ($node !== null) {
            if ($node instanceof Element) {
                $extensionScripts = $this->addRequiredExtensionByTag($document, $node, $extensionScripts);
                $extensionScripts = $this->addRequiredExtensionByAttributes($document, $node, $extensionScripts);
            }

            $node = NodeWalker::nextNode($node);
        }

        return $extensionScripts;
    }

    /**
     * Add required extensions by tag names.
     *
     * @param Document  $document         Document to scan for missing extensions.
     * @param Element   $node             The node we are inspecting to see if it needs an extension.
     * @param Element[] $extensionScripts Array of preexisting extension scripts.
     * @return Element[] Adapted array of extension scripts that includes the previously missing ones.
     */
    private function addRequiredExtensionByTag(Document $document, Element $node, $extensionScripts)
    {
        if (! Amp::isCustomElement($node)) {
            return $this->addExtensionForCustomTemplates($document, $node, $extensionScripts);
        }

        $tagSpecs = $this->spec->tags()->byTagName($node->tagName);

        foreach ($tagSpecs as $tagSpec) {
            foreach ($tagSpec->requiresExtension as $requiredExtension) {
                if (in_array($requiredExtension, self::MANUAL_ATTRIBUTE_TO_EXTENSION_MAPPING, true)) {
                    continue;
                }

                $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, $requiredExtension);
            }
        }

        return $extensionScripts;
    }

    /**
     * Add extensions custom templates (e.g. amp-mustache).
     *
     * @param Document  $document         Document to scan for missing extensions.
     * @param Element   $node             The node we are inspecting to see if it needs an extension.
     * @param Element[] $extensionScripts Array of preexisting extension scripts.
     * @return Element[] Adapted array of extension scripts that includes the previously missing ones.
     */
    private function addExtensionForCustomTemplates(Document $document, Element $node, $extensionScripts)
    {
        $requiredExtension = '';

        if ($node->tagName === Tag::TEMPLATE && $node->hasAttribute(Attribute::TYPE)) {
            $requiredExtension = $node->getAttribute(Attribute::TYPE);
        } elseif ($node->tagName === Tag::SCRIPT && $node->hasAttribute(Attribute::TEMPLATE)) {
            $requiredExtension = $node->getAttribute(Attribute::TEMPLATE);
        } elseif ($node->tagName === Tag::INPUT && $node->hasAttribute(Attribute::MASK)) {
            $requiredExtension = Extension::INPUTMASK;
        }

        return ! $requiredExtension ? $extensionScripts : $this->maybeAddExtension(
            $document,
            $extensionScripts,
            $requiredExtension
        );
    }

    /**
     * Add required extension by attributes.
     *
     * @param Document  $document         Document to scan for missing extensions.
     * @param Element   $node             The node we are inspecting to see if it needs an extension.
     * @param Element[] $extensionScripts Array of preexisting extension scripts.
     * @return Element[] Adapted array of extension scripts that includes the previously missing ones.
     */
    private function addRequiredExtensionByAttributes(Document $document, Element $node, $extensionScripts)
    {
        if (! $node->hasAttributes()) {
            return $extensionScripts;
        }

        // The amp-form extension extends the regular <form> tag, so we manually look for that tag.
        // See https://amp.dev/documentation/components/amp-form/.
        if ($node->tagName === Tag::FORM) {
            $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, Extension::FORM);
        }

        // Will be set to true if we need to import amp-bind.
        $usesAmpBind = false;

        // Associative array of new attribute names with data-amp-bind- prefix and attribute value pair.
        $newAttributes = [];

        // Populate all attributes for the current node tag name.
        $nodeAttributeList = $this->getTagAttributeList($node->tagName);

        $globalAttributes = $this->spec->attributeLists()->get(GlobalAttrs::ID);

        foreach ($node->attributes as $attribute) {
            // Add attribute dependencies (e.g. amp-img => lightbox => amp-lightbox-gallery).
            if (array_key_exists($attribute->name, self::MANUAL_ATTRIBUTE_TO_EXTENSION_MAPPING)) {
                $extensionScripts = $this->maybeAddExtension(
                    $document,
                    $extensionScripts,
                    self::MANUAL_ATTRIBUTE_TO_EXTENSION_MAPPING[$attribute->name]
                );
            }

            // Element attributes that require amp extensions (e.g. amp-video[dock]).
            if (! empty($nodeAttributeList[$attribute->name][SpecRule::REQUIRES_EXTENSION])) {
                $requiresExtensions = $nodeAttributeList[$attribute->name][SpecRule::REQUIRES_EXTENSION];
                foreach ($requiresExtensions as $requiresExtension) {
                    $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, $requiresExtension);
                }
            }

            // Attributes that require AMP components (e.g. amp-fx).
            if ($globalAttributes->has($attribute->name)) {
                $attr = $globalAttributes->get($attribute->name);

                if (isset($attr[SpecRule::REQUIRES_EXTENSION])) {
                    foreach ($attr[SpecRule::REQUIRES_EXTENSION] as $requiresExtension) {
                        $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, $requiresExtension);
                    }
                }
            }

            // Check for amp-bind attribute bindings.
            if (strpos($attribute->name, '[') === 0 || strpos($attribute->name, Amp::BIND_DATA_ATTR_PREFIX) === 0) {
                $usesAmpBind = true;
            }

            /*
             * EXPERIMENTAL FEATURE: Rewrite short-form `bindtext` to `data-amp-bind-text`.
             *
             * To avoid false-positives, we only check the supported bindable attributes for each tag (e.g. for a div
             * only bindtext, but not bindvalue).
             */
            if (
                $this->configuration->get(AutoExtensionsConfiguration::EXPERIMENT_BIND_ATTRIBUTE)
                && strpos($attribute->name, self::BIND_SHORT_FORM_PREFIX) === 0
            ) {
                // Change name from bindaria-labelledby to aria-labelledby.
                $attributeNameWithoutBindPrefix = substr($attribute->name, strlen(self::BIND_SHORT_FORM_PREFIX));

                // Change the name from aria-labelledby to [ARIA_LABELLEDBY].
                $bindableAttributeName = '['
                    . str_replace('-', '_', strtoupper($attributeNameWithoutBindPrefix))
                    . ']';

                // Rename attribute from bindx to data-amp-bind-x.
                if ($globalAttributes->has($bindableAttributeName)) {
                    $newAttributeName = Amp::BIND_DATA_ATTR_PREFIX . $attributeNameWithoutBindPrefix;
                    $newAttributes[$newAttributeName] = $attribute->value;

                    $node->removeAttribute($attribute->name);

                    $usesAmpBind = true;
                }
            }
        }

        // Add the renamed bindable attributes in the node.
        if ($newAttributes) {
            $node->setAttributes($newAttributes);
        }

        if ($usesAmpBind) {
            $extensionScripts = $this->maybeAddExtension($document, $extensionScripts, Extension::BIND);
        }

        return $extensionScripts;
    }

    /**
     * Get all attributes for a tag.
     *
     * @TODO: Provide this functionality as part of the validator spec implementation.
     *
     * @param string $tagName Name of the node tag.
     * @return array Attribute list for the tag name.
     */
    private function getTagAttributeList($tagName)
    {
        static $nodeAttributeList = [];

        if (! isset($nodeAttributeList[$tagName])) {
            $nodeAttributeList[$tagName] = [];

            $tagSpecs = $this->spec->tags()->byTagName($tagName);

            foreach ($tagSpecs as $tagSpec) {
                $attrLists = $tagSpec->get(SpecRule::ATTR_LISTS);
                if (is_array($attrLists)) {
                    foreach ($attrLists as $attrList) {
                        $list = $this->spec->attributeLists()->get($attrList);
                        $nodeAttributeList[$tagName] = array_merge($nodeAttributeList[$tagName], $list::ATTRIBUTES);
                    }
                }
            }
        }

        return $nodeAttributeList[$tagName];
    }

    /**
     * Remove unneeded extension scripts.
     *
     * @param Element[] $extensionScripts Array of extension scripts to check.
     * @return Element[] Adapted array of extension scripts.
     */
    private function removeUnneededExtensions($extensionScripts)
    {
        if (! $this->configuration->get(AutoExtensionsConfiguration::REMOVE_UNNEEDED_EXTENSIONS)) {
            return $extensionScripts;
        }

        return array_filter($extensionScripts, function ($extension) {
            return array_key_exists($extension, $this->addedExtensions)
                || $this->isProtectedExtension($extension);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Determines whether an extension should not be removed even when there is no usage in the HTML.
     *
     * @param string $extension Name of the extension to be checked.
     * @return bool Whether the extension should be protected.
     */
    private function isProtectedExtension($extension)
    {
        if (array_key_exists($extension, $this->protectedExtensions)) {
            if (empty($this->protectedExtensions[$extension])) {
                return true;
            }

            foreach ($this->protectedExtensions[$extension] as $dependentExtension) {
                if (array_key_exists($dependentExtension, $this->addedExtensions)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Render the extension scripts into the DOM document.
     *
     * @param Document $document         Document to render the extension scripts into.
     * @param array    $extensionScripts Array of extension scripts to render.
     */
    private function renderExtensionScripts(Document $document, array $extensionScripts)
    {
        $referenceNode = $this->getExtensionScriptsReferenceNode($document);

        foreach ($extensionScripts as $extensionScript) {
            if ($referenceNode && $referenceNode->nextSibling) {
                $referenceNode->parentNode->insertBefore(
                    $extensionScript,
                    $referenceNode->nextSibling
                );
            } else {
                $document->head->appendChild($extensionScript);
            }

            $referenceNode = $extensionScript;
        }
    }

    /**
     * Get the reference node to attach extension scripts to.
     *
     * @param Document $document Document to look for the reference node in.
     * @return Element|null Reference node to use, or null if not found.
     */
    private function getExtensionScriptsReferenceNode(Document $document)
    {
        $referenceNode = $document->viewport ?: $document->charset;

        if (! $referenceNode instanceof Element) {
            $referenceNode = $document->head->firstChild;
        }

        if (! $referenceNode instanceof Element) {
            return null;
        }

        // Try to detect the boilerplate style so we can append the scripts after that.
        $remainingNode = $referenceNode->nextSibling;
        while ($remainingNode) {
            if (! $remainingNode instanceof Element) {
                $remainingNode = $remainingNode->nextSibling;
                continue;
            }

            if (
                $remainingNode->tagName === Tag::STYLE
                && $remainingNode->hasAttribute(Attribute::AMP_BOILERPLATE)
            ) {
                $referenceNode = $remainingNode;
            } elseif (
                $remainingNode->tagName === Tag::NOSCRIPT
                && $remainingNode->firstChild instanceof Element
                && $remainingNode->firstChild->tagName === Tag::STYLE
                && $remainingNode->firstChild->hasAttribute(Attribute::AMP_BOILERPLATE)
            ) {
                $referenceNode = $remainingNode;
            }

            $remainingNode = $remainingNode->nextSibling;
        }

        return $referenceNode;
    }

    /**
     * Maybe add a required extension to the list of extension scripts.
     *
     * @param Document $document          Document to render the extension scripts into.
     * @param array    $extensionScripts  Existing list of extension scripts.
     * @param string   $requiredExtension Required extension to check for.
     * @return array Adapted list of extension scripts.
     */
    private function maybeAddExtension(Document $document, $extensionScripts, $requiredExtension)
    {
        if (
            in_array(
                $requiredExtension,
                $this->configuration->get(AutoExtensionsConfiguration::IGNORED_EXTENSIONS),
                true
            )
        ) {
            return $extensionScripts;
        }

        if (! array_key_exists($requiredExtension, $extensionScripts)) {
            $tagSpecs = $this->spec->tags()->byExtensionSpec($requiredExtension);

            foreach ($tagSpecs as $tagSpec) {
                $requiredScript = $document->createElement(Tag::SCRIPT);
                $requiredScript->appendChild($document->createAttribute(Attribute::ASYNC));
                $requiredScript->setAttribute($tagSpec->getExtensionType(), $requiredExtension);
                $requiredScript->setAttribute(Attribute::SRC, $this->getScriptSrcForExtension($tagSpec));
                $extensionScripts[$requiredExtension] = $requiredScript;
            }
        }

        $this->addedExtensions[$requiredExtension] = true;

        return $extensionScripts;
    }

    /**
     * Get the URL to use for the extension script's src attribute.
     *
     * @param Spec\TagWithExtensionSpec $tagSpec Spec of the extension tag.
     * @return string URL to use for extension script.
     */
    private function getScriptSrcForExtension($tagSpec)
    {
        $version = $this->calculateExtensionVersion($tagSpec);
        return Amp::CACHE_ROOT_URL . "v0/{$tagSpec->getExtensionName()}-{$version}.js";
    }

    /**
     * Calculate the extension version.
     *
     * @param Spec\TagWithExtensionSpec $tagSpec Spec of the extension tag.
     * @return string Extension version.
     */
    private function calculateExtensionVersion($tagSpec)
    {
        $configuredVersions = $this->configuration->get(AutoExtensionsConfiguration::EXTENSION_VERSIONS);

        if (! empty($configuredVersions[$tagSpec->getExtensionName()])) {
            return $configuredVersions[$tagSpec->getExtensionName()];
        }

        return $tagSpec->getLatestVersion();
    }

    /**
     * Get the JSON data from the script element.
     *
     * @param Element         $script Script element to get the JSON data from.
     * @param ErrorCollection $errors Collection of errors that are collected during transformation.
     * @return array Associative array of parsed JSON data.
     */
    private function getJsonData(Element $script, ErrorCollection $errors)
    {
        try {
            $jsonString = trim($script->nodeValue);

            if (empty($jsonString)) {
                return [];
            }

            $jsonData = json_decode(trim($script->nodeValue), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidArgumentException('json_decode error: ' . json_last_error_msg());
            }

            if (is_array($jsonData)) {
                return $jsonData;
            }
        } catch (Exception $exception) {
            $errors->add(CannotParseJsonData::fromExceptionForScriptElement($exception, $script));
        }

        return [];
    }
}
