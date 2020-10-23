<?php
/**
 * Class FileReflector.
 *
 * This class is based on code from the WordPress/phpdoc-parser project by
 * Ryan McCue, Paul Gibbs, Andrey "Rarst" Savchenko and Contributors,
 * licensed under the GPLv2 or later.
 *
 * @link https://github.com/WordPress/phpdoc-parser
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Parser;

use phpDocumentor\Reflection;
use phpDocumentor\Reflection\BaseReflector;
use phpDocumentor\Reflection\FileReflector as PhpDocumentorFileReflector;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeAbstract;

/**
 * Reflection class for a full file.
 *
 * Extends the FileReflector from phpDocumentor to parse out WordPress
 * hooks and note function relationships.
 */
final class FileReflector extends PhpDocumentorFileReflector {

	/**
	 * List of elements used in global scope in this file, indexed by element
	 * type.
	 *
	 * @var array {
	 *     @type HookReflector[]         $hooks     The action and filters.
	 *     @type FunctionCallReflector[] $functions The functions called.
	 * }
	 */
	public $uses = [];

	/**
	 * List of elements used in the current class scope, indexed by method.
	 *
	 * @var array[][] {@see self::$uses}
	 */
	protected $method_uses_queue = [];

	/**
	 * Stack of classes/methods/functions currently being parsed.
	 *
	 * @see self::getLocation()
	 * @var BaseReflector[]
	 */
	protected $location = [];

	/**
	 * Last DocBlock associated with a non-documentable element.
	 *
	 * @var Doc
	 */
	protected $last_doc;

	/**
	 * Add hooks to the queue and update the node stack when we enter a node.
	 *
	 * If we are entering a class, function or method, we push it to the
	 * location stack. This is just so that we know whether we are in the file
	 * scope or not, so that hooks in the main file scope can be added to the
	 * file.
	 *
	 * We also check function calls to see if there are any actions or hooks.
	 * If there are, they are added to the file's hooks if in the global scope,
	 * or if we are in a function/method, they are added to the queue. They will
	 * be assigned to the function by leaveNode(). We also check for any other
	 * function calls and treat them similarly, so that we can export a list of
	 * functions used by each element.
	 *
	 * Finally, we pick up any docblocks for nodes that usually aren't
	 * documentable, so they can be assigned to the hooks to which they may
	 * belong.
	 *
	 * @param Node|NodeAbstract $node Node that is being entered.
	 */
	public function enterNode( Node $node ) {
		parent::enterNode( $node );

		switch ( $node->getType() ) {
			// Add classes, functions, and methods to the current location stack.
			case 'Stmt_Class':
			case 'Stmt_Function':
			case 'Stmt_ClassMethod':
				$this->location[] = $node;
				break;

			// Parse out hook definitions and function calls and add them to the queue.
			case 'Expr_FuncCall':
				$function = new FunctionCallReflector( $node, $this->context );

				// Add the call to the list of functions used in this scope.
				$this->getLocation()->uses['functions'][] = $function;

				if ( $this->isFilter( $node ) ) {
					if ( $this->last_doc && ! $node->getDocComment() ) {
						$node->setAttribute( 'comments', [ $this->last_doc ] );
						$this->last_doc = null;
					}

					$hook = new HookReflector( $node, $this->context );

					// Add it to the list of hooks used in this scope.
					$this->getLocation()->uses['hooks'][] = $hook;
				}
				break;

			// Parse out method calls, so we can export where methods are used.
			case 'Expr_MethodCall':
				$method = new MethodCallReflector( $node, $this->context );

				// Add it to the list of methods used in this scope.
				$this->getLocation()->uses['methods'][] = $method;
				break;

			// Parse out method calls, so we can export where methods are used.
			case 'Expr_StaticCall':
				$method = new StaticMethodCallReflector( $node, $this->context );

				// Add it to the list of methods used in this scope.
				$this->getLocation()->uses['methods'][] = $method;
				break;

			// Parse out `new Class()` calls as uses of Class::__construct().
			case 'Expr_New':
				$method = new MethodCallReflector( $node, $this->context );

				// Add it to the list of methods used in this scope.
				$this->getLocation()->uses['methods'][] = $method;
				break;
		}

		// Pick up DocBlock from non-documentable elements so that it can be assigned
		// to the next hook if necessary. We don't do this for name nodes, since even
		// though they aren't documentable, they still carry the docblock from their
		// corresponding class/constant/function/etc. that they are the name of. If
		// we don't ignore them, we'll end up picking up docblocks that are already
		// associated with a named element, and so aren't really from a non-
		// documentable element after all.
		if (
			! $this->isNodeDocumentable( $node )
			&& 'Name' !== $node->getType()
			&& ( null !== $node->getDocComment() )
		) {
			$this->last_doc = $node->getDocComment();
		}
	}

	/**
	 * Assign queued hooks to functions and update the node stack on leaving a
	 * node.
	 *
	 * We can now access the function/method reflectors, so we can assign any
	 * queued hooks to them. The reflector for a node isn't created until the
	 * node is left.
	 *
	 * @param Node $node Node that is being left.
	 */
	public function leaveNode( Node $node ) {
		parent::leaveNode( $node );

		switch ( $node->getType() ) {
			case 'Stmt_Class':
				$class = end( $this->classes );
				if ( ! empty( $this->method_uses_queue ) ) {
					/** @var Reflection\ClassReflector\MethodReflector $method */
					foreach ( $class->getMethods() as $method ) {
						if ( isset( $this->method_uses_queue[ $method->getName() ] ) ) {
							if ( isset( $this->method_uses_queue[ $method->getName() ]['methods'] ) ) {
								/*
								 * For methods used in a class, set the class on the method call.
								 * That allows us to later get the correct class name for $this, self, parent.
								 */
								foreach ( $this->method_uses_queue[ $method->getName() ]['methods'] as $method_call ) {
									/** @var MethodCallReflector $method_call */
									$method_call->set_class( $class );
								}
							}

							$method->uses = $this->method_uses_queue[ $method->getName() ];
						}
					}
				}

				$this->method_uses_queue = [];
				array_pop( $this->location );
				break;

			case 'Stmt_Function':
				$location = array_pop( $this->location );
				if ( property_exists( $location, 'uses' ) ) {
					end( $this->functions )->uses = $location->uses;
				}
				break;

			case 'Stmt_ClassMethod':
				$method = array_pop( $this->location );

				/*
				 * Store the list of elements used by this method in the queue. We'll
				 * assign them to the method upon leaving the class (see above).
				 */
				if ( ! empty( $method->uses ) ) {
					$this->method_uses_queue[ $method->name ] = $method->uses;
				}
				break;
		}
	}

	/**
	 * Check whether a given node is a filter.
	 *
	 * @param Node $node Node to check.
	 *
	 * @return bool Whether the node is a filter.
	 */
	protected function isFilter( Node $node ) {
		// Ignore variable functions.
		if ( 'Name' !== $node->name->getType() ) {
			return false;
		}

		$calling = (string) $node->name;

		$functions = [
			'apply_filters',
			'apply_filters_ref_array',
			'apply_filters_deprecated',
			'do_action',
			'do_action_ref_array',
			'do_action_deprecated',
		];

		return in_array( $calling, $functions, true );
	}

	/**
	 * Get the current location.
	 *
	 * @return self Current location.
	 */
	protected function getLocation() {
		return empty( $this->location ) ? $this : end( $this->location );
	}

	/**
	 * Check whether a given node is documentable.
	 *
	 * @param Node $node Node to check.
	 *
	 * @return bool Whether the given node is documentable.
	 */
	protected function isNodeDocumentable( Node $node ) {
		return parent::isNodeDocumentable( $node )
			||
			(
				$node instanceof FuncCall
				&&
				$this->isFilter( $node )
			);
	}
}
