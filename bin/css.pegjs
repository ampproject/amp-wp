// Forked from https://github.com/pegjs/pegjs/blob/b6bc0d905e8c1a1c9cdb825d03980a06a668f5b8/examples/css.pegjs
// CSS Grammar
// ===========
//
// Based on grammar from CSS 2.1 specification [1] (including the errata [2]).
// Generated parser builds a syntax tree composed of nested JavaScript objects,
// vaguely inspired by CSS DOM [3]. The CSS DOM itself wasn't used as it is not
// expressive enough (e.g. selectors are reflected as text, not structured
// objects) and somewhat cumbersome.
//
// Limitations:
//
//   * Many errors which should be recovered from according to the specification
//     (e.g. malformed declarations or unexpected end of stylesheet) are fatal.
//     This is a result of straightforward rewrite of the CSS grammar to PEG.js.
//
// [1] http://www.w3.org/TR/2011/REC-CSS2-20110607
// [2] http://www.w3.org/Style/css2-updates/REC-CSS2-20110607-errata.html
// [3] http://www.w3.org/TR/DOM-Level-2-Style/css.html

{
  function extractOptional($optional, $index) {
    return $optional ? $optional[$index] : null;
  }

  function extractList($list, $index) {
    $mapped = array();
    foreach ( $list as $element ) {
      $mapped[] = $element[ $index ];
    }
    return $mapped;
  }

  function buildList($head, $tail, $index) {
    return array_merge( $head, array_filter( extractList( $tail, $index ) ) );
  }

  function buildExpression($head, $tail) {
    return array_reduce(
      $tail,
      function ( $result, $element ) {
        return array(
          'type' => "Expression",
          'operator' => $element[0],
          'left' => $result,
          'right' => $element[1]
        );
      },
      $head
    );
  }
}

start
  = stylesheet:stylesheet comment* { return $stylesheet; }

// ----- G.1 Grammar -----

stylesheet
  = charset:(CHARSET_SYM STRING ";")? (S / CDO / CDC)*
    imports:(import (CDO S* / CDC S*)*)*
    rules:((ruleset / media / page) (CDO S* / CDC S*)*)*
    {
      return array(
        'type' => "StyleSheet",
        'charset' => extractOptional($charset, 1),
        'imports' => extractList($imports, 0),
        'rules' => extractList($rules, 0)
      );
    }

import
  = IMPORT_SYM S* href:(STRING / URI) S* media:media_list? ";" S* {
      return array(
        'type' => "ImportRule",
        'href' => $href,
        'media' => $media !== null ? $media : []
      );
    }

media
  = MEDIA_SYM S* media:media_list "{" S* rules:ruleset* "}" S* {
      return array(
        'type' => "MediaRule",
        'media' => $media,
        'rules' => $rules
      );
    }

media_list
  = head:medium tail:("," S* medium)* { return $buildList($head, $tail, 2); }

medium
  = name:IDENT_ S* { return $name; }

page
  = PAGE_SYM S* selector:pseudo_page?
    "{" S*
    declarationsHead:declaration?
    declarationsTail:(";" S* declaration?)*
    "}" S*
    {
      return array(
        'type' => "PageRule",
        'selector' => $selector,
        'declarations' => buildList($declarationsHead, $declarationsTail, 2)
      );
    }

pseudo_page
  = ":" value:IDENT_ S* { return array( 'type' => "PseudoSelector", 'value' => $value ); }

operator
  = "/" S* { return "/"; }
  / "," S* { return ","; }

combinator
  = "+" S* { return "+"; }
  / ">" S* { return ">"; }

property
  = name:IDENT_ S* { return $name; }

ruleset
  = selectorsHead:selector
    selectorsTail:("," S* selector)*
    "{" S*
    declarationsHead:declaration?
    declarationsTail:(";" S* declaration?)*
    "}" S*
    {
      return array(
        'type' => "RuleSet",
        'selectors' => buildList($selectorsHead, $selectorsTail, 2),
        'declarations' => buildList($declarationsHead, $declarationsTail, 2)
      );
    }

selector
  = left:simple_selector S* combinator:combinator right:selector {
      return array(
        'type' => "Selector",
        'combinator' => $combinator,
        'left' => $left,
        'right' => $right
      );
    }
  / left:simple_selector S+ right:selector {
      return array(
        'type' => "Selector",
        'combinator' => " ",
        'left' => $left,
        'right' => $right
      );
    }
  / selector:simple_selector S* { return $selector; }

simple_selector
  = element:element_name qualifiers:(id / class / attrib / pseudo)* {
      return array(
        'type' => "SimpleSelector",
        'element' => $element,
        'qualifiers' => $qualifiers
      );
    }
  / qualifiers:(id / class / attrib / pseudo)+ {
      return array(
        'type' => "SimpleSelector",
        'element' => "*",
        'qualifiers' => $qualifiers
      );
    }

id
  = id:HASH { return array( 'type' => "IDSelector", 'id' => $id ); }

class
  = "." class_:IDENT_ { return array( 'type' => "ClassSelector", "class" => $class_ ); }

element_name
  = IDENT_
  / "*"

attrib
  = "[" S*
    attribute:IDENT_ S*
    operatorAndValue:(("=" / INCLUDES / DASHMATCH) S* (IDENT_ / STRING) S*)?
    "]"
    {
      return array(
        'type' => "AttributeSelector",
        'attribute' => $attribute,
        'operator' => extractOptional($operatorAndValue, 0),
        'value' => extractOptional($operatorAndValue, 2)
      );
    }

pseudo
  = ":"
    value:(
        name:FUNCTION_ S* params:(IDENT_ S*)? ")" {
          return array(
            'type' => "Function",
            'name' => name,
            'params' => params !== null ? array( $params[0] ) : array()
          );
        }
      / IDENT_
    )
    { return array( 'type' => "PseudoSelector", 'value' => $value ); }

declaration
  = name:property ':' S* value:expr prio:prio? {
      return array(
        'type' => "Declaration",
        'name' => $name,
        'value' => $value,
        'important' => $prio !== null
      );
    }

prio
  = IMPORTANT_SYM S*

expr
  = head:term tail:(operator? term)* { return buildExpression($head, $tail); }

term
  = quantity:(PERCENTAGE / LENGTH / EMS / EXS / ANGLE / TIME / FREQ / NUMBER)
    S*
    {
      return array(
        'type' => "Quantity",
        'value' => $quantity['value'],
        'unit' => $quantity['unit']
      );
    }
  / value:STRING S* { return array( 'type' => "String", 'value' => $value ); }
  / value:URI S*    { return array( 'type' => "URI",    'value' => $value ); }
  / function
  / hexcolor
  / value:IDENT_ S*  { return array( 'type' => "Ident",  'value' => $value ); }

function
  = name:FUNCTION_ S* params:expr ")" S* {
      return array( 'type' => "Function", 'name' => $name, 'params' => $params );
    }

hexcolor
  = value:HASH S* { return array( 'type' => "Hexcolor", 'value' => $value ); }

// ----- G.2 Lexical scanner -----

// Macros

hex
  = [0-9a-f]i

nonascii
  = [\x80-\uFFFF]

unicode
  = "\\" digits:$(hex hex? hex? hex? hex? hex?) ("\r\n" / [ \t\r\n\f])? {
      return chr_unicode(intval(digits, 16));
    }

escape
  = unicode
  / "\\" ch:[^\r\n\f0-9a-f]i { return ch; }

nmstart
  = [_a-z]i
  / nonascii
  / escape

nmchar
  = [_a-z0-9-]i
  / nonascii
  / escape

string1
  = '"' chars:([^\n\r\f\\"] / "\\" nl:nl { return ""; } / escape)* '"' {
      return join( '', $chars );
    }

string2
  = "'" chars:([^\n\r\f\\'] / "\\" nl:nl { return ""; } / escape)* "'" {
      return join( '', $chars );
    }

comment
  = "/*" [^*]* "*"+ ([^/*] [^*]* "*"+)* "/"

ident
  = prefix:$"-"? start:nmstart chars:nmchar* {
      return $prefix . $start . join( '', $chars );
    }

name
  = chars:nmchar+ { return join( '', $chars ); }

num
  = [+-]? ([0-9]* "." [0-9]+ / [0-9]+) ("e" [+-]? [0-9]+)? {
      return floatval($this->text());
    }

string_
  = string1
  / string2

url
  = chars:([!#$%&*-\[\]-~] / nonascii / escape)* { return join( '', $chars ); }

space
  = [ \t\r\n\f]+

w
  = space?

nl
  = "\n"
  / "\r\n"
  / "\r"
  / "\f"

A  = "a"i / "\\" "0"? "0"? "0"? "0"? [\x41\x61] ("\r\n" / [ \t\r\n\f])? { return "a"; }
C  = "c"i / "\\" "0"? "0"? "0"? "0"? [\x43\x63] ("\r\n" / [ \t\r\n\f])? { return "c"; }
D  = "d"i / "\\" "0"? "0"? "0"? "0"? [\x44\x64] ("\r\n" / [ \t\r\n\f])? { return "d"; }
E  = "e"i / "\\" "0"? "0"? "0"? "0"? [\x45\x65] ("\r\n" / [ \t\r\n\f])? { return "e"; }
G  = "g"i / "\\" "0"? "0"? "0"? "0"? [\x47\x67] ("\r\n" / [ \t\r\n\f])? / "\\g"i { return "g"; }
H  = "h"i / "\\" "0"? "0"? "0"? "0"? [\x48\x68] ("\r\n" / [ \t\r\n\f])? / "\\h"i { return "h"; }
I  = "i"i / "\\" "0"? "0"? "0"? "0"? [\x49\x69] ("\r\n" / [ \t\r\n\f])? / "\\i"i { return "i"; }
K  = "k"i / "\\" "0"? "0"? "0"? "0"? [\x4b\x6b] ("\r\n" / [ \t\r\n\f])? / "\\k"i { return "k"; }
L  = "l"i / "\\" "0"? "0"? "0"? "0"? [\x4c\x6c] ("\r\n" / [ \t\r\n\f])? / "\\l"i { return "l"; }
M  = "m"i / "\\" "0"? "0"? "0"? "0"? [\x4d\x6d] ("\r\n" / [ \t\r\n\f])? / "\\m"i { return "m"; }
N  = "n"i / "\\" "0"? "0"? "0"? "0"? [\x4e\x6e] ("\r\n" / [ \t\r\n\f])? / "\\n"i { return "n"; }
O  = "o"i / "\\" "0"? "0"? "0"? "0"? [\x4f\x6f] ("\r\n" / [ \t\r\n\f])? / "\\o"i { return "o"; }
P  = "p"i / "\\" "0"? "0"? "0"? "0"? [\x50\x70] ("\r\n" / [ \t\r\n\f])? / "\\p"i { return "p"; }
R  = "r"i / "\\" "0"? "0"? "0"? "0"? [\x52\x72] ("\r\n" / [ \t\r\n\f])? / "\\r"i { return "r"; }
S_ = "s"i / "\\" "0"? "0"? "0"? "0"? [\x53\x73] ("\r\n" / [ \t\r\n\f])? / "\\s"i { return "s"; }
T  = "t"i / "\\" "0"? "0"? "0"? "0"? [\x54\x74] ("\r\n" / [ \t\r\n\f])? / "\\t"i { return "t"; }
U  = "u"i / "\\" "0"? "0"? "0"? "0"? [\x55\x75] ("\r\n" / [ \t\r\n\f])? / "\\u"i { return "u"; }
X  = "x"i / "\\" "0"? "0"? "0"? "0"? [\x58\x78] ("\r\n" / [ \t\r\n\f])? / "\\x"i { return "x"; }
Z  = "z"i / "\\" "0"? "0"? "0"? "0"? [\x5a\x7a] ("\r\n" / [ \t\r\n\f])? / "\\z"i { return "z"; }

// Tokens

S "whitespace"
  = comment* space

CDO "<!--"
  = comment* "<!--"

CDC "-->"
  = comment* "-->"

INCLUDES "~="
  = comment* "~="

DASHMATCH "|="
  = comment* "|="

STRING "string"
  = comment* string_:string_ { return $string; }

IDENT_ "identifier"
  = comment* ident:ident { return $ident; }

HASH "hash"
  = comment* "#" name:name { return "#" . $name; }

IMPORT_SYM "@import"
  = comment* "@" I M P O R T

PAGE_SYM "@page"
  = comment* "@" P A G E

MEDIA_SYM "@media"
  = comment* "@" M E D I A

CHARSET_SYM "@charset"
  = comment* "@charset "

// We use |space| instead of |w| here to avoid infinite recursion.
IMPORTANT_SYM "!important"
  = comment* "!" (space / comment)* I M P O R T A N T

EMS "length"
  = comment* value:num E M { return array( 'value' => $value, 'unit' => "em" ); }

EXS "length"
  = comment* value:num E X { return array( 'value' => $value, 'unit' => "ex" ); }

LENGTH "length"
  = comment* value:num P X { return array( 'value' => $value, 'unit' => "px" ); }
  / comment* value:num C M { return array( 'value' => $value, 'unit' => "cm" ); }
  / comment* value:num M M { return array( 'value' => $value, 'unit' => "mm" ); }
  / comment* value:num I N { return array( 'value' => $value, 'unit' => "in" ); }
  / comment* value:num P T { return array( 'value' => $value, 'unit' => "pt" ); }
  / comment* value:num P C { return array( 'value' => $value, 'unit' => "pc" ); }

ANGLE "angle"
  = comment* value:num D E G   { return array( 'value' => $value, 'unit' => "deg"  ); }
  / comment* value:num R A D   { return array( 'value' => $value, 'unit' => "rad"  ); }
  / comment* value:num G R A D { return array( 'value' => $value, 'unit' => "grad" ); }

TIME "time"
  = comment* value:num M S_ { return array( 'value' => $value, 'unit' => "ms" ); }
  / comment* value:num S_   { return array( 'value' => $value, 'unit' => "s"  ); }

FREQ "frequency"
  = comment* value:num H Z   { return array( 'value' => $value, 'unit' => "hz" ); }
  / comment* value:num K H Z { return array( 'value' => $value, 'unit' => "kh" ); }

PERCENTAGE "percentage"
  = comment* value:num "%" { return array( 'value' => $value, 'unit' => "%" ); }

NUMBER "number"
  = comment* value:num { return array( 'value' => $value, 'unit' => null ); }

URI "uri"
  = comment* U R L "("i w url:string_ w ")" { return $url; }
  / comment* U R L "("i w url:url w ")"    { return $url; }

FUNCTION_ "function"
  = comment* name:ident "(" { return $name; }
