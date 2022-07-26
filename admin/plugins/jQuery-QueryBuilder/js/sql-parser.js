/*!
 * SQLParser (v1.3.0)
 * @copyright 2012-2015 Andy Kent <andy@forward.co.uk>
 * @copyright 2015-2019 Damien "Mistic" Sorel <contact@git.strangeplanet.fr>
 * @licence MIT
 */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
	typeof define === 'function' && define.amd ? define(['exports'], factory) :
	(global = global || self, factory(global.SQLParser = {}));
}(this, (function (exports) { 'use strict';

	function createCommonjsModule(fn, module) {
		return module = { exports: {} }, fn(module, module.exports), module.exports;
	}

	var SQL_FUNCTIONS = ['AVG', 'COUNT', 'MIN', 'MAX', 'SUM'];
	var SQL_SORT_ORDERS = ['ASC', 'DESC'];
	var SQL_OPERATORS = ['=', '!=', '>=', '>', '<=', '<>', '<', 'LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE', 'IS NOT', 'IS', 'REGEXP', 'NOT REGEXP'];
	var SUB_SELECT_OP = ['IN', 'NOT IN', 'ANY', 'ALL', 'SOME'];
	var SUB_SELECT_UNARY_OP = ['EXISTS'];
	var SQL_CONDITIONALS = ['AND', 'OR'];
	var SQL_BETWEENS = ['BETWEEN', 'NOT BETWEEN'];
	var BOOLEAN = ['TRUE', 'FALSE', 'NULL'];
	var MATH = ['+', '-', '||', '&&'];
	var MATH_MULTI = ['/', '*'];
	var STAR = /^\*/;
	var SEPARATOR = /^,/;
	var WHITESPACE = /^[ \n\r]+/;
	var LITERAL = /^`?([a-z_][a-z0-9_]{0,}(\:(number|float|string|date|boolean))?)`?/i;
	var PARAMETER = /^\$([a-z0-9_]+(\:(number|float|string|date|boolean))?)/;
	var NUMBER = /^[+-]?[0-9]+(\.[0-9]+)?/;
	var STRING = /^'((?:[^\\']+?|\\.|'')*)'(?!')/;
	var DBLSTRING = /^"([^\\"]*(?:\\.[^\\"]*)*)"/;

	var Lexer =
	/*#__PURE__*/
	function () {
	  function Lexer(sql, opts) {
	    if (opts === void 0) {
	      opts = {};
	    }

	    this.sql = sql;
	    this.preserveWhitespace = opts.preserveWhitespace || false;
	    this.tokens = [];
	    this.currentLine = 1;
	    this.currentOffset = 0;
	    var i = 0;

	    while (!!(this.chunk = sql.slice(i))) {
	      var bytesConsumed = this.keywordToken() || this.starToken() || this.booleanToken() || this.functionToken() || this.windowExtension() || this.sortOrderToken() || this.seperatorToken() || this.operatorToken() || this.numberToken() || this.mathToken() || this.dotToken() || this.conditionalToken() || this.betweenToken() || this.subSelectOpToken() || this.subSelectUnaryOpToken() || this.stringToken() || this.parameterToken() || this.parensToken() || this.whitespaceToken() || this.literalToken();

	      if (bytesConsumed < 1) {
	        throw new Error("NOTHING CONSUMED: Stopped at - '" + this.chunk.slice(0, 30) + "'");
	      }

	      i += bytesConsumed;
	      this.currentOffset += bytesConsumed;
	    }

	    this.token('EOF', '');
	    this.postProcess();
	  }

	  var _proto = Lexer.prototype;

	  _proto.postProcess = function postProcess() {
	    var results = [];

	    for (var _i = 0, j = 0, len = this.tokens.length; j < len; _i = ++j) {
	      var token = this.tokens[_i];

	      if (token[0] === 'STAR') {
	        var next_token = this.tokens[_i + 1];

	        if (!(next_token[0] === 'SEPARATOR' || next_token[0] === 'FROM')) {
	          results.push(token[0] = 'MATH_MULTI');
	        } else {
	          results.push(void 0);
	        }
	      } else {
	        results.push(void 0);
	      }
	    }

	    return results;
	  };

	  _proto.token = function token(name, value) {
	    return this.tokens.push([name, value, this.currentLine, this.currentOffset]);
	  };

	  _proto.tokenizeFromStringRegex = function tokenizeFromStringRegex(name, regex, part, lengthPart, output) {
	    if (part === void 0) {
	      part = 0;
	    }

	    if (lengthPart === void 0) {
	      lengthPart = part;
	    }

	    if (output === void 0) {
	      output = true;
	    }

	    var match = regex.exec(this.chunk);

	    if (!match) {
	      return 0;
	    }

	    var partMatch = match[part].replace(/''/g, '\'');

	    if (output) {
	      this.token(name, partMatch);
	    }

	    return match[lengthPart].length;
	  };

	  _proto.tokenizeFromRegex = function tokenizeFromRegex(name, regex, part, lengthPart, output) {
	    if (part === void 0) {
	      part = 0;
	    }

	    if (lengthPart === void 0) {
	      lengthPart = part;
	    }

	    if (output === void 0) {
	      output = true;
	    }

	    var match = regex.exec(this.chunk);

	    if (!match) {
	      return 0;
	    }

	    var partMatch = match[part];

	    if (output) {
	      this.token(name, partMatch);
	    }

	    return match[lengthPart].length;
	  };

	  _proto.tokenizeFromWord = function tokenizeFromWord(name, word) {
	    if (word === void 0) {
	      word = name;
	    }

	    word = this.regexEscape(word);
	    var matcher = /^\w+$/.test(word) ? new RegExp("^(" + word + ")\\b", 'ig') : new RegExp("^(" + word + ")", 'ig');
	    var match = matcher.exec(this.chunk);

	    if (!match) {
	      return 0;
	    }

	    this.token(name, match[1]);
	    return match[1].length;
	  };

	  _proto.tokenizeFromList = function tokenizeFromList(name, list) {
	    var ret = 0;

	    for (var j = 0, len = list.length; j < len; j++) {
	      var entry = list[j];
	      ret = this.tokenizeFromWord(name, entry);

	      if (ret > 0) {
	        break;
	      }
	    }

	    return ret;
	  };

	  _proto.keywordToken = function keywordToken() {
	    return this.tokenizeFromWord('SELECT') || this.tokenizeFromWord('INSERT') || this.tokenizeFromWord('INTO') || this.tokenizeFromWord('DEFAULT') || this.tokenizeFromWord('VALUES') || this.tokenizeFromWord('DISTINCT') || this.tokenizeFromWord('FROM') || this.tokenizeFromWord('WHERE') || this.tokenizeFromWord('GROUP') || this.tokenizeFromWord('ORDER') || this.tokenizeFromWord('BY') || this.tokenizeFromWord('HAVING') || this.tokenizeFromWord('LIMIT') || this.tokenizeFromWord('JOIN') || this.tokenizeFromWord('LEFT') || this.tokenizeFromWord('RIGHT') || this.tokenizeFromWord('INNER') || this.tokenizeFromWord('OUTER') || this.tokenizeFromWord('ON') || this.tokenizeFromWord('AS') || this.tokenizeFromWord('CASE') || this.tokenizeFromWord('WHEN') || this.tokenizeFromWord('THEN') || this.tokenizeFromWord('ELSE') || this.tokenizeFromWord('END') || this.tokenizeFromWord('UNION') || this.tokenizeFromWord('ALL') || this.tokenizeFromWord('LIMIT') || this.tokenizeFromWord('OFFSET') || this.tokenizeFromWord('FETCH') || this.tokenizeFromWord('ROW') || this.tokenizeFromWord('ROWS') || this.tokenizeFromWord('ONLY') || this.tokenizeFromWord('NEXT') || this.tokenizeFromWord('FIRST');
	  };

	  _proto.dotToken = function dotToken() {
	    return this.tokenizeFromWord('DOT', '.');
	  };

	  _proto.operatorToken = function operatorToken() {
	    return this.tokenizeFromList('OPERATOR', SQL_OPERATORS);
	  };

	  _proto.mathToken = function mathToken() {
	    return this.tokenizeFromList('MATH', MATH) || this.tokenizeFromList('MATH_MULTI', MATH_MULTI);
	  };

	  _proto.conditionalToken = function conditionalToken() {
	    return this.tokenizeFromList('CONDITIONAL', SQL_CONDITIONALS);
	  };

	  _proto.betweenToken = function betweenToken() {
	    return this.tokenizeFromList('BETWEEN', SQL_BETWEENS);
	  };

	  _proto.subSelectOpToken = function subSelectOpToken() {
	    return this.tokenizeFromList('SUB_SELECT_OP', SUB_SELECT_OP);
	  };

	  _proto.subSelectUnaryOpToken = function subSelectUnaryOpToken() {
	    return this.tokenizeFromList('SUB_SELECT_UNARY_OP', SUB_SELECT_UNARY_OP);
	  };

	  _proto.functionToken = function functionToken() {
	    return this.tokenizeFromList('FUNCTION', SQL_FUNCTIONS);
	  };

	  _proto.sortOrderToken = function sortOrderToken() {
	    return this.tokenizeFromList('DIRECTION', SQL_SORT_ORDERS);
	  };

	  _proto.booleanToken = function booleanToken() {
	    return this.tokenizeFromList('BOOLEAN', BOOLEAN);
	  };

	  _proto.starToken = function starToken() {
	    return this.tokenizeFromRegex('STAR', STAR);
	  };

	  _proto.seperatorToken = function seperatorToken() {
	    return this.tokenizeFromRegex('SEPARATOR', SEPARATOR);
	  };

	  _proto.literalToken = function literalToken() {
	    return this.tokenizeFromRegex('LITERAL', LITERAL, 1, 0);
	  };

	  _proto.numberToken = function numberToken() {
	    return this.tokenizeFromRegex('NUMBER', NUMBER);
	  };

	  _proto.parameterToken = function parameterToken() {
	    return this.tokenizeFromRegex('PARAMETER', PARAMETER, 1, 0);
	  };

	  _proto.stringToken = function stringToken() {
	    return this.tokenizeFromStringRegex('STRING', STRING, 1, 0) || this.tokenizeFromRegex('DBLSTRING', DBLSTRING, 1, 0);
	  };

	  _proto.parensToken = function parensToken() {
	    return this.tokenizeFromRegex('LEFT_PAREN', /^\(/) || this.tokenizeFromRegex('RIGHT_PAREN', /^\)/);
	  };

	  _proto.windowExtension = function windowExtension() {
	    var match = /^\.(win):(length|time)/i.exec(this.chunk);

	    if (!match) {
	      return 0;
	    }

	    this.token('WINDOW', match[1]);
	    this.token('WINDOW_FUNCTION', match[2]);
	    return match[0].length;
	  };

	  _proto.whitespaceToken = function whitespaceToken() {
	    var match = WHITESPACE.exec(this.chunk);

	    if (!match) {
	      return 0;
	    }

	    var partMatch = match[0];

	    if (this.preserveWhitespace) {
	      this.token('WHITESPACE', partMatch);
	    }

	    var newlines = partMatch.match(/\n/g, '');
	    this.currentLine += (newlines != null ? newlines.length : void 0) || 0;
	    return partMatch.length;
	  };

	  _proto.regexEscape = function regexEscape(str) {
	    return str.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
	  };

	  return Lexer;
	}();

	var tokenize = function tokenize(sql, opts) {
	  return new Lexer(sql, opts).tokens;
	};

	var lexer = {
	  tokenize: tokenize
	};

	/* parser generated by jison 0.4.18 */

	/*
	  Returns a Parser object of the following structure:

	  Parser: {
	    yy: {}
	  }

	  Parser.prototype: {
	    yy: {},
	    trace: function(),
	    symbols_: {associative list: name ==> number},
	    terminals_: {associative list: number ==> name},
	    productions_: [...],
	    performAction: function anonymous(yytext, yyleng, yylineno, yy, yystate, $$, _$),
	    table: [...],
	    defaultActions: {...},
	    parseError: function(str, hash),
	    parse: function(input),

	    lexer: {
	        EOF: 1,
	        parseError: function(str, hash),
	        setInput: function(input),
	        input: function(),
	        unput: function(str),
	        more: function(),
	        less: function(n),
	        pastInput: function(),
	        upcomingInput: function(),
	        showPosition: function(),
	        test_match: function(regex_match_array, rule_index),
	        next: function(),
	        lex: function(),
	        begin: function(condition),
	        popState: function(),
	        _currentRules: function(),
	        topState: function(),
	        pushState: function(condition),

	        options: {
	            ranges: boolean           (optional: true ==> token location info will include a .range[] member)
	            flex: boolean             (optional: true ==> flex-like lexing behaviour where the rules are tested exhaustively to find the longest match)
	            backtrack_lexer: boolean  (optional: true ==> lexer regexes are tested in order and for each matching regex the action code is invoked; the lexer terminates the scan when a token is returned by the action code)
	        },

	        performAction: function(yy, yy_, $avoiding_name_collisions, YY_START),
	        rules: [...],
	        conditions: {associative list: name ==> set},
	    }
	  }


	  token location info (@$, _$, etc.): {
	    first_line: n,
	    last_line: n,
	    first_column: n,
	    last_column: n,
	    range: [start_number, end_number]       (where the numbers are indexes into the input string, regular zero-based)
	  }


	  the parseError function receives a 'hash' object with these members for lexer and parser errors: {
	    text:        (matched text)
	    token:       (the produced terminal token, if any)
	    line:        (yylineno)
	  }
	  while parser (grammar) errors will also provide these members, i.e. parser errors deliver a superset of attributes: {
	    loc:         (yylloc)
	    expected:    (string describing the set of expected tokens)
	    recoverable: (boolean: TRUE when the parser has a error recovery rule available for this particular error)
	  }
	*/
	var parser = function () {
	  var o = function o(k, v, _o, l) {
	    for (_o = _o || {}, l = k.length; l--; _o[k[l]] = v) {
	    }

	    return _o;
	  },
	      $V0 = [1, 8],
	      $V1 = [5, 26],
	      $V2 = [1, 14],
	      $V3 = [1, 13],
	      $V4 = [5, 26, 31, 42],
	      $V5 = [1, 17],
	      $V6 = [5, 26, 31, 42, 45, 62],
	      $V7 = [1, 27],
	      $V8 = [1, 29],
	      $V9 = [1, 40],
	      $Va = [1, 42],
	      $Vb = [1, 46],
	      $Vc = [1, 47],
	      $Vd = [1, 43],
	      $Ve = [1, 44],
	      $Vf = [1, 41],
	      $Vg = [1, 45],
	      $Vh = [1, 25],
	      $Vi = [5, 26, 31],
	      $Vj = [5, 26, 31, 42, 45],
	      $Vk = [1, 59],
	      $Vl = [18, 43],
	      $Vm = [1, 62],
	      $Vn = [1, 63],
	      $Vo = [1, 64],
	      $Vp = [1, 65],
	      $Vq = [1, 66],
	      $Vr = [5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 45, 62, 64, 65, 66, 67, 68, 70, 78, 81, 82, 83],
	      $Vs = [5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 44, 45, 51, 62, 64, 65, 66, 67, 68, 70, 71, 78, 81, 82, 83, 89, 90, 91, 92, 93, 94, 96],
	      $Vt = [1, 74],
	      $Vu = [1, 77],
	      $Vv = [2, 93],
	      $Vw = [1, 91],
	      $Vx = [1, 92],
	      $Vy = [5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 45, 62, 64, 65, 66, 67, 68, 70, 78, 81, 82, 83, 89, 90, 91, 92, 93, 94, 96],
	      $Vz = [78, 81, 83],
	      $VA = [1, 116],
	      $VB = [5, 26, 31, 42, 43, 44],
	      $VC = [1, 124],
	      $VD = [5, 26, 31, 42, 43, 45, 64],
	      $VE = [5, 26, 31, 41, 42, 45, 62],
	      $VF = [1, 127],
	      $VG = [1, 128],
	      $VH = [1, 129],
	      $VI = [5, 26, 31, 34, 35, 37, 38, 41, 42, 45, 62],
	      $VJ = [5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 45, 62, 64, 70, 78, 81, 82, 83],
	      $VK = [5, 26, 31, 34, 37, 38, 41, 42, 45, 62],
	      $VL = [5, 26, 31, 42, 56, 58];

	  var parser = {
	    trace: function trace() {},
	    yy: {},
	    symbols_: {
	      "error": 2,
	      "Root": 3,
	      "Query": 4,
	      "EOF": 5,
	      "SelectQuery": 6,
	      "Unions": 7,
	      "SelectWithLimitQuery": 8,
	      "BasicSelectQuery": 9,
	      "Select": 10,
	      "OrderClause": 11,
	      "GroupClause": 12,
	      "LimitClause": 13,
	      "SelectClause": 14,
	      "WhereClause": 15,
	      "SELECT": 16,
	      "Fields": 17,
	      "FROM": 18,
	      "Table": 19,
	      "DISTINCT": 20,
	      "Joins": 21,
	      "Literal": 22,
	      "AS": 23,
	      "LEFT_PAREN": 24,
	      "List": 25,
	      "RIGHT_PAREN": 26,
	      "WINDOW": 27,
	      "WINDOW_FUNCTION": 28,
	      "Number": 29,
	      "Union": 30,
	      "UNION": 31,
	      "ALL": 32,
	      "Join": 33,
	      "JOIN": 34,
	      "ON": 35,
	      "Expression": 36,
	      "LEFT": 37,
	      "RIGHT": 38,
	      "INNER": 39,
	      "OUTER": 40,
	      "WHERE": 41,
	      "LIMIT": 42,
	      "SEPARATOR": 43,
	      "OFFSET": 44,
	      "ORDER": 45,
	      "BY": 46,
	      "OrderArgs": 47,
	      "OffsetClause": 48,
	      "OrderArg": 49,
	      "Value": 50,
	      "DIRECTION": 51,
	      "OffsetRows": 52,
	      "FetchClause": 53,
	      "ROW": 54,
	      "ROWS": 55,
	      "FETCH": 56,
	      "FIRST": 57,
	      "ONLY": 58,
	      "NEXT": 59,
	      "GroupBasicClause": 60,
	      "HavingClause": 61,
	      "GROUP": 62,
	      "ArgumentList": 63,
	      "HAVING": 64,
	      "MATH": 65,
	      "MATH_MULTI": 66,
	      "OPERATOR": 67,
	      "BETWEEN": 68,
	      "BetweenExpression": 69,
	      "CONDITIONAL": 70,
	      "SUB_SELECT_OP": 71,
	      "SubSelectExpression": 72,
	      "SUB_SELECT_UNARY_OP": 73,
	      "WhitepaceList": 74,
	      "CaseStatement": 75,
	      "CASE": 76,
	      "CaseWhens": 77,
	      "END": 78,
	      "CaseElse": 79,
	      "CaseWhen": 80,
	      "WHEN": 81,
	      "THEN": 82,
	      "ELSE": 83,
	      "String": 84,
	      "Function": 85,
	      "UserFunction": 86,
	      "Boolean": 87,
	      "Parameter": 88,
	      "NUMBER": 89,
	      "BOOLEAN": 90,
	      "PARAMETER": 91,
	      "STRING": 92,
	      "DBLSTRING": 93,
	      "LITERAL": 94,
	      "DOT": 95,
	      "FUNCTION": 96,
	      "AggregateArgumentList": 97,
	      "Case": 98,
	      "Field": 99,
	      "STAR": 100,
	      "$accept": 0,
	      "$end": 1
	    },
	    terminals_: {
	      2: "error",
	      5: "EOF",
	      16: "SELECT",
	      18: "FROM",
	      20: "DISTINCT",
	      23: "AS",
	      24: "LEFT_PAREN",
	      26: "RIGHT_PAREN",
	      27: "WINDOW",
	      28: "WINDOW_FUNCTION",
	      31: "UNION",
	      32: "ALL",
	      34: "JOIN",
	      35: "ON",
	      37: "LEFT",
	      38: "RIGHT",
	      39: "INNER",
	      40: "OUTER",
	      41: "WHERE",
	      42: "LIMIT",
	      43: "SEPARATOR",
	      44: "OFFSET",
	      45: "ORDER",
	      46: "BY",
	      51: "DIRECTION",
	      54: "ROW",
	      55: "ROWS",
	      56: "FETCH",
	      57: "FIRST",
	      58: "ONLY",
	      59: "NEXT",
	      62: "GROUP",
	      64: "HAVING",
	      65: "MATH",
	      66: "MATH_MULTI",
	      67: "OPERATOR",
	      68: "BETWEEN",
	      70: "CONDITIONAL",
	      71: "SUB_SELECT_OP",
	      73: "SUB_SELECT_UNARY_OP",
	      76: "CASE",
	      78: "END",
	      81: "WHEN",
	      82: "THEN",
	      83: "ELSE",
	      89: "NUMBER",
	      90: "BOOLEAN",
	      91: "PARAMETER",
	      92: "STRING",
	      93: "DBLSTRING",
	      94: "LITERAL",
	      95: "DOT",
	      96: "FUNCTION",
	      98: "Case",
	      100: "STAR"
	    },
	    productions_: [0, [3, 2], [4, 1], [4, 2], [6, 1], [6, 1], [9, 1], [9, 2], [9, 2], [9, 3], [8, 2], [10, 1], [10, 2], [14, 4], [14, 5], [14, 5], [14, 6], [19, 1], [19, 2], [19, 3], [19, 3], [19, 3], [19, 4], [19, 6], [7, 1], [7, 2], [30, 2], [30, 3], [21, 1], [21, 2], [33, 4], [33, 5], [33, 5], [33, 6], [33, 6], [33, 6], [33, 6], [15, 2], [13, 2], [13, 4], [13, 4], [11, 3], [11, 4], [47, 1], [47, 3], [49, 1], [49, 2], [48, 2], [48, 3], [52, 2], [52, 2], [53, 4], [53, 4], [12, 1], [12, 2], [60, 3], [61, 2], [36, 3], [36, 3], [36, 3], [36, 3], [36, 3], [36, 3], [36, 5], [36, 3], [36, 2], [36, 1], [36, 1], [36, 1], [36, 1], [69, 3], [75, 3], [75, 4], [80, 4], [77, 2], [77, 1], [79, 2], [72, 3], [50, 1], [50, 1], [50, 1], [50, 1], [50, 1], [50, 1], [50, 1], [74, 2], [74, 2], [25, 1], [29, 1], [87, 1], [88, 1], [84, 1], [84, 1], [22, 1], [22, 3], [85, 4], [86, 3], [86, 4], [86, 4], [97, 1], [97, 2], [63, 1], [63, 3], [17, 1], [17, 3], [99, 1], [99, 1], [99, 3]],
	    performAction: function anonymous(yytext, yyleng, yylineno, yy, yystate
	    /* action[1] */
	    , $$
	    /* vstack */
	    , _$
	    /* lstack */
	    ) {
	      /* this == yyval */
	      var $0 = $$.length - 1;

	      switch (yystate) {
	        case 1:
	          return this.$ = $$[$0 - 1];

	        case 2:
	        case 4:
	        case 5:
	        case 6:
	        case 11:
	        case 53:
	        case 66:
	        case 68:
	        case 69:
	        case 78:
	        case 79:
	        case 80:
	        case 81:
	        case 82:
	        case 83:
	        case 84:
	          this.$ = $$[$0];
	          break;

	        case 3:
	          this.$ = function () {
	            $$[$0 - 1].unions = $$[$0];
	            return $$[$0 - 1];
	          }();

	          break;

	        case 7:
	          this.$ = function () {
	            $$[$0 - 1].order = $$[$0];
	            return $$[$0 - 1];
	          }();

	          break;

	        case 8:
	          this.$ = function () {
	            $$[$0 - 1].group = $$[$0];
	            return $$[$0 - 1];
	          }();

	          break;

	        case 9:
	          this.$ = function () {
	            $$[$0 - 2].group = $$[$0 - 1];
	            $$[$0 - 2].order = $$[$0];
	            return $$[$0 - 2];
	          }();

	          break;

	        case 10:
	          this.$ = function () {
	            $$[$0 - 1].limit = $$[$0];
	            return $$[$0 - 1];
	          }();

	          break;

	        case 12:
	          this.$ = function () {
	            $$[$0 - 1].where = $$[$0];
	            return $$[$0 - 1];
	          }();

	          break;

	        case 13:
	          this.$ = new yy.Select($$[$0 - 2], $$[$0], false);
	          break;

	        case 14:
	          this.$ = new yy.Select($$[$0 - 2], $$[$0], true);
	          break;

	        case 15:
	          this.$ = new yy.Select($$[$0 - 3], $$[$0 - 1], false, $$[$0]);
	          break;

	        case 16:
	          this.$ = new yy.Select($$[$0 - 3], $$[$0 - 1], true, $$[$0]);
	          break;

	        case 17:
	          this.$ = new yy.Table($$[$0]);
	          break;

	        case 18:
	          this.$ = new yy.Table($$[$0 - 1], $$[$0]);
	          break;

	        case 19:
	          this.$ = new yy.Table($$[$0 - 2], $$[$0]);
	          break;

	        case 20:
	        case 49:
	        case 50:
	        case 51:
	        case 52:
	        case 57:
	          this.$ = $$[$0 - 1];
	          break;

	        case 21:
	        case 77:
	          this.$ = new yy.SubSelect($$[$0 - 1]);
	          break;

	        case 22:
	          this.$ = new yy.SubSelect($$[$0 - 2], $$[$0]);
	          break;

	        case 23:
	          this.$ = new yy.Table($$[$0 - 5], null, $$[$0 - 4], $$[$0 - 3], $$[$0 - 1]);
	          break;

	        case 24:
	        case 28:
	        case 43:
	        case 75:
	        case 101:
	        case 103:
	          this.$ = [$$[$0]];
	          break;

	        case 25:
	        case 29:
	        case 74:
	          this.$ = $$[$0 - 1].concat($$[$0]);
	          break;

	        case 26:
	          this.$ = new yy.Union($$[$0]);
	          break;

	        case 27:
	          this.$ = new yy.Union($$[$0], true);
	          break;

	        case 30:
	          this.$ = new yy.Join($$[$0 - 2], $$[$0]);
	          break;

	        case 31:
	          this.$ = new yy.Join($$[$0 - 2], $$[$0], 'LEFT');
	          break;

	        case 32:
	          this.$ = new yy.Join($$[$0 - 2], $$[$0], 'RIGHT');
	          break;

	        case 33:
	          this.$ = new yy.Join($$[$0 - 2], $$[$0], 'LEFT', 'INNER');
	          break;

	        case 34:
	          this.$ = new yy.Join($$[$0 - 2], $$[$0], 'RIGHT', 'INNER');
	          break;

	        case 35:
	          this.$ = new yy.Join($$[$0 - 2], $$[$0], 'LEFT', 'OUTER');
	          break;

	        case 36:
	          this.$ = new yy.Join($$[$0 - 2], $$[$0], 'RIGHT', 'OUTER');
	          break;

	        case 37:
	          this.$ = new yy.Where($$[$0]);
	          break;

	        case 38:
	          this.$ = new yy.Limit($$[$0]);
	          break;

	        case 39:
	          this.$ = new yy.Limit($$[$0], $$[$0 - 2]);
	          break;

	        case 40:
	          this.$ = new yy.Limit($$[$0 - 2], $$[$0]);
	          break;

	        case 41:
	          this.$ = new yy.Order($$[$0]);
	          break;

	        case 42:
	          this.$ = new yy.Order($$[$0 - 1], $$[$0]);
	          break;

	        case 44:
	        case 102:
	        case 104:
	          this.$ = $$[$0 - 2].concat($$[$0]);
	          break;

	        case 45:
	          this.$ = new yy.OrderArgument($$[$0], 'ASC');
	          break;

	        case 46:
	          this.$ = new yy.OrderArgument($$[$0 - 1], $$[$0]);
	          break;

	        case 47:
	          this.$ = new yy.Offset($$[$0]);
	          break;

	        case 48:
	          this.$ = new yy.Offset($$[$0 - 1], $$[$0]);
	          break;

	        case 54:
	          this.$ = function () {
	            $$[$0 - 1].having = $$[$0];
	            return $$[$0 - 1];
	          }();

	          break;

	        case 55:
	          this.$ = new yy.Group($$[$0]);
	          break;

	        case 56:
	          this.$ = new yy.Having($$[$0]);
	          break;

	        case 58:
	        case 59:
	        case 60:
	        case 61:
	        case 62:
	        case 64:
	          this.$ = new yy.Op($$[$0 - 1], $$[$0 - 2], $$[$0]);
	          break;

	        case 63:
	          this.$ = new yy.Op($$[$0 - 3], $$[$0 - 4], $$[$0 - 1]);
	          break;

	        case 65:
	          this.$ = new yy.UnaryOp($$[$0 - 1], $$[$0]);
	          break;

	        case 67:
	          this.$ = new yy.WhitepaceList($$[$0]);
	          break;

	        case 70:
	          this.$ = new yy.BetweenOp([$$[$0 - 2], $$[$0]]);
	          break;

	        case 71:
	          this.$ = new yy.Case($$[$0 - 1]);
	          break;

	        case 72:
	          this.$ = new yy.Case($$[$0 - 2], $$[$0 - 1]);
	          break;

	        case 73:
	          this.$ = new yy.CaseWhen($$[$0 - 2], $$[$0]);
	          break;

	        case 76:
	          this.$ = new yy.CaseElse($$[$0]);
	          break;

	        case 85:
	          this.$ = [$$[$0 - 1], $$[$0]];
	          break;

	        case 86:
	          this.$ = function () {
	            $$[$0 - 1].push($$[$0]);
	            return $$[$0 - 1];
	          }();

	          break;

	        case 87:
	          this.$ = new yy.ListValue($$[$0]);
	          break;

	        case 88:
	          this.$ = new yy.NumberValue($$[$0]);
	          break;

	        case 89:
	          this.$ = new yy.BooleanValue($$[$0]);
	          break;

	        case 90:
	          this.$ = new yy.ParameterValue($$[$0]);
	          break;

	        case 91:
	          this.$ = new yy.StringValue($$[$0], "'");
	          break;

	        case 92:
	          this.$ = new yy.StringValue($$[$0], '"');
	          break;

	        case 93:
	          this.$ = new yy.LiteralValue($$[$0]);
	          break;

	        case 94:
	          this.$ = new yy.LiteralValue($$[$0 - 2], $$[$0]);
	          break;

	        case 95:
	          this.$ = new yy.FunctionValue($$[$0 - 3], $$[$0 - 1]);
	          break;

	        case 96:
	          this.$ = new yy.FunctionValue($$[$0 - 2], null, true);
	          break;

	        case 97:
	        case 98:
	          this.$ = new yy.FunctionValue($$[$0 - 3], $$[$0 - 1], true);
	          break;

	        case 99:
	          this.$ = new yy.ArgumentListValue($$[$0]);
	          break;

	        case 100:
	          this.$ = new yy.ArgumentListValue($$[$0], true);
	          break;

	        case 105:
	          this.$ = new yy.Star();
	          break;

	        case 106:
	          this.$ = new yy.Field($$[$0]);
	          break;

	        case 107:
	          this.$ = new yy.Field($$[$0 - 2], $$[$0]);
	          break;
	      }
	    },
	    table: [{
	      3: 1,
	      4: 2,
	      6: 3,
	      8: 4,
	      9: 5,
	      10: 6,
	      14: 7,
	      16: $V0
	    }, {
	      1: [3]
	    }, {
	      5: [1, 9]
	    }, o($V1, [2, 2], {
	      7: 10,
	      13: 11,
	      30: 12,
	      31: $V2,
	      42: $V3
	    }), o($V4, [2, 4]), o($V4, [2, 5]), o($V4, [2, 6], {
	      11: 15,
	      12: 16,
	      60: 18,
	      45: $V5,
	      62: [1, 19]
	    }), o($V6, [2, 11], {
	      15: 20,
	      41: [1, 21]
	    }), {
	      17: 22,
	      20: [1, 23],
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 26,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg,
	      99: 24,
	      100: $Vh
	    }, {
	      1: [2, 1]
	    }, o($V1, [2, 3], {
	      30: 48,
	      31: $V2
	    }), o($V4, [2, 10]), o($Vi, [2, 24]), {
	      29: 49,
	      89: $Va
	    }, {
	      6: 50,
	      8: 4,
	      9: 5,
	      10: 6,
	      14: 7,
	      16: $V0,
	      32: [1, 51]
	    }, o($V4, [2, 7]), o($V4, [2, 8], {
	      11: 52,
	      45: $V5
	    }), {
	      46: [1, 53]
	    }, o($Vj, [2, 53], {
	      61: 54,
	      64: [1, 55]
	    }), {
	      46: [1, 56]
	    }, o($V6, [2, 12]), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 57,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      18: [1, 58],
	      43: $Vk
	    }, {
	      17: 60,
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 26,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg,
	      99: 24,
	      100: $Vh
	    }, o($Vl, [2, 103]), o($Vl, [2, 105]), o($Vl, [2, 106], {
	      23: [1, 61],
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), {
	      4: 68,
	      6: 3,
	      8: 4,
	      9: 5,
	      10: 6,
	      14: 7,
	      16: $V0,
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 67,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($Vr, [2, 69], {
	      22: 33,
	      29: 34,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      50: 70,
	      71: [1, 69],
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }), {
	      24: [1, 72],
	      72: 71
	    }, o($Vr, [2, 66]), o($Vr, [2, 67], {
	      22: 33,
	      29: 34,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      50: 73,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }), o($Vr, [2, 68]), o($Vs, [2, 78], {
	      95: $Vt
	    }), o($Vs, [2, 79]), o($Vs, [2, 80]), o($Vs, [2, 81]), o($Vs, [2, 82]), o($Vs, [2, 83]), o($Vs, [2, 84]), {
	      77: 75,
	      80: 76,
	      81: $Vu
	    }, o([5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 44, 45, 51, 62, 64, 65, 66, 67, 68, 70, 71, 78, 81, 82, 83, 89, 90, 91, 92, 93, 94, 95, 96], $Vv, {
	      24: [1, 78]
	    }), o([5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 44, 45, 51, 54, 55, 62, 64, 65, 66, 67, 68, 70, 71, 78, 81, 82, 83, 89, 90, 91, 92, 93, 94, 96], [2, 88]), o($Vs, [2, 91]), o($Vs, [2, 92]), {
	      24: [1, 79]
	    }, o($Vs, [2, 89]), o($Vs, [2, 90]), o($Vi, [2, 25]), o($V4, [2, 38], {
	      43: [1, 80],
	      44: [1, 81]
	    }), o($Vi, [2, 26], {
	      13: 11,
	      42: $V3
	    }), {
	      6: 82,
	      8: 4,
	      9: 5,
	      10: 6,
	      14: 7,
	      16: $V0
	    }, o($V4, [2, 9]), {
	      22: 33,
	      29: 34,
	      47: 83,
	      49: 84,
	      50: 85,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($Vj, [2, 54]), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 86,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 88,
	      50: 28,
	      63: 87,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($V6, [2, 37], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), {
	      19: 89,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 26,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg,
	      99: 93,
	      100: $Vh
	    }, {
	      18: [1, 94],
	      43: $Vk
	    }, {
	      22: 95,
	      94: $Vx
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 96,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 97,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 98,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 100,
	      50: 28,
	      69: 99,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 101,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      26: [1, 102],
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }, {
	      26: [1, 103]
	    }, {
	      24: [1, 104],
	      72: 105
	    }, o($Vy, [2, 85]), o($Vr, [2, 65]), {
	      4: 68,
	      6: 3,
	      8: 4,
	      9: 5,
	      10: 6,
	      14: 7,
	      16: $V0
	    }, o($Vy, [2, 86]), {
	      94: [1, 106]
	    }, {
	      78: [1, 107],
	      79: 108,
	      80: 109,
	      81: $Vu,
	      83: [1, 110]
	    }, o($Vz, [2, 75]), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 111,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      20: $VA,
	      22: 33,
	      24: $V7,
	      26: [1, 112],
	      29: 34,
	      36: 88,
	      50: 28,
	      63: 115,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg,
	      97: 113,
	      98: [1, 114]
	    }, {
	      20: $VA,
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 88,
	      50: 28,
	      63: 115,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg,
	      97: 117
	    }, {
	      29: 118,
	      89: $Va
	    }, {
	      29: 119,
	      89: $Va
	    }, o($Vi, [2, 27], {
	      13: 11,
	      42: $V3
	    }), o($V4, [2, 41], {
	      48: 120,
	      43: [1, 121],
	      44: [1, 122]
	    }), o($VB, [2, 43]), o($VB, [2, 45], {
	      51: [1, 123]
	    }), o($Vj, [2, 56], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), o([5, 26, 31, 42, 45, 64], [2, 55], {
	      43: $VC
	    }), o($VD, [2, 101], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), o($VE, [2, 13], {
	      21: 125,
	      33: 126,
	      34: $VF,
	      37: $VG,
	      38: $VH
	    }), o($VI, [2, 17], {
	      22: 130,
	      23: [1, 131],
	      27: [1, 132],
	      94: $Vx,
	      95: $Vt
	    }), {
	      4: 134,
	      6: 3,
	      8: 4,
	      9: 5,
	      10: 6,
	      14: 7,
	      16: $V0,
	      22: 33,
	      24: $V7,
	      25: 133,
	      29: 34,
	      36: 88,
	      50: 28,
	      63: 135,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o([5, 18, 23, 26, 27, 31, 34, 35, 37, 38, 41, 42, 43, 45, 62, 94, 95], $Vv), o($Vl, [2, 104]), {
	      19: 136,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, o($Vl, [2, 107], {
	      95: $Vt
	    }), o([5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 45, 62, 64, 65, 67, 70, 78, 81, 82, 83], [2, 58], {
	      66: $Vn,
	      68: $Vp
	    }), o([5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 45, 62, 64, 65, 66, 67, 70, 78, 81, 82, 83], [2, 59], {
	      68: $Vp
	    }), o([5, 18, 23, 26, 31, 34, 37, 38, 41, 42, 43, 45, 62, 64, 67, 70, 78, 81, 82, 83], [2, 60], {
	      65: $Vm,
	      66: $Vn,
	      68: $Vp
	    }), o($Vr, [2, 61]), {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: [1, 137]
	    }, o($VJ, [2, 62], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp
	    }), o($Vr, [2, 57]), o($Vr, [2, 77]), {
	      4: 68,
	      6: 3,
	      8: 4,
	      9: 5,
	      10: 6,
	      14: 7,
	      16: $V0,
	      22: 33,
	      24: $V7,
	      25: 138,
	      29: 34,
	      36: 88,
	      50: 28,
	      63: 135,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($Vr, [2, 64]), o([5, 18, 23, 26, 27, 31, 34, 35, 37, 38, 41, 42, 43, 44, 45, 51, 62, 64, 65, 66, 67, 68, 70, 71, 78, 81, 82, 83, 89, 90, 91, 92, 93, 94, 95, 96], [2, 94]), o($Vr, [2, 71]), {
	      78: [1, 139]
	    }, o($Vz, [2, 74]), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 140,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq,
	      82: [1, 141]
	    }, o($Vs, [2, 96]), {
	      26: [1, 142]
	    }, {
	      26: [1, 143]
	    }, {
	      26: [2, 99],
	      43: $VC
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 88,
	      50: 28,
	      63: 144,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      26: [1, 145]
	    }, o($V4, [2, 39]), o($V4, [2, 40]), o($V4, [2, 42]), {
	      22: 33,
	      29: 34,
	      49: 146,
	      50: 85,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      29: 148,
	      52: 147,
	      89: $Va
	    }, o($VB, [2, 46]), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 149,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($VE, [2, 15], {
	      33: 150,
	      34: $VF,
	      37: $VG,
	      38: $VH
	    }), o($VK, [2, 28]), {
	      19: 151,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      34: [1, 152],
	      39: [1, 153],
	      40: [1, 154]
	    }, {
	      34: [1, 155],
	      39: [1, 156],
	      40: [1, 157]
	    }, o($VI, [2, 18], {
	      95: $Vt
	    }), {
	      22: 158,
	      94: $Vx
	    }, {
	      28: [1, 159]
	    }, {
	      26: [1, 160]
	    }, {
	      26: [1, 161]
	    }, {
	      26: [2, 87],
	      43: $VC
	    }, o($VE, [2, 14], {
	      33: 126,
	      21: 162,
	      34: $VF,
	      37: $VG,
	      38: $VH
	    }), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 163,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      26: [1, 164]
	    }, o($Vr, [2, 72]), {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq,
	      78: [2, 76]
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 165,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($Vs, [2, 97]), o($Vs, [2, 98]), {
	      26: [2, 100],
	      43: $VC
	    }, o($Vs, [2, 95]), o($VB, [2, 44]), o($V4, [2, 47], {
	      53: 166,
	      56: [1, 167]
	    }), {
	      54: [1, 168],
	      55: [1, 169]
	    }, o($VD, [2, 102], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), o($VK, [2, 29]), {
	      35: [1, 170]
	    }, {
	      19: 171,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      34: [1, 172]
	    }, {
	      34: [1, 173]
	    }, {
	      19: 174,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      34: [1, 175]
	    }, {
	      34: [1, 176]
	    }, o($VI, [2, 19], {
	      95: $Vt
	    }), {
	      24: [1, 177]
	    }, o($VI, [2, 20]), o($VI, [2, 21], {
	      22: 178,
	      94: $Vx
	    }), o($VE, [2, 16], {
	      33: 150,
	      34: $VF,
	      37: $VG,
	      38: $VH
	    }), o($VJ, [2, 70], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp
	    }), o($Vr, [2, 63]), o($Vz, [2, 73], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), o($V4, [2, 48]), {
	      57: [1, 179],
	      59: [1, 180]
	    }, o($VL, [2, 49]), o($VL, [2, 50]), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 181,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      35: [1, 182]
	    }, {
	      19: 183,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      19: 184,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      35: [1, 185]
	    }, {
	      19: 186,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      19: 187,
	      22: 90,
	      24: $Vw,
	      94: $Vx
	    }, {
	      29: 188,
	      89: $Va
	    }, o($VI, [2, 22], {
	      95: $Vt
	    }), {
	      29: 148,
	      52: 189,
	      89: $Va
	    }, {
	      29: 148,
	      52: 190,
	      89: $Va
	    }, o($VK, [2, 30], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 191,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      35: [1, 192]
	    }, {
	      35: [1, 193]
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 194,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      35: [1, 195]
	    }, {
	      35: [1, 196]
	    }, {
	      26: [1, 197]
	    }, {
	      58: [1, 198]
	    }, {
	      58: [1, 199]
	    }, o($VK, [2, 31], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 200,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 201,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($VK, [2, 32], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 202,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, {
	      22: 33,
	      24: $V7,
	      29: 34,
	      36: 203,
	      50: 28,
	      72: 30,
	      73: $V8,
	      74: 31,
	      75: 32,
	      76: $V9,
	      84: 35,
	      85: 36,
	      86: 37,
	      87: 38,
	      88: 39,
	      89: $Va,
	      90: $Vb,
	      91: $Vc,
	      92: $Vd,
	      93: $Ve,
	      94: $Vf,
	      96: $Vg
	    }, o($VI, [2, 23]), o($V4, [2, 51]), o($V4, [2, 52]), o($VK, [2, 33], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), o($VK, [2, 35], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), o($VK, [2, 34], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    }), o($VK, [2, 36], {
	      65: $Vm,
	      66: $Vn,
	      67: $Vo,
	      68: $Vp,
	      70: $Vq
	    })],
	    defaultActions: {
	      9: [2, 1]
	    },
	    parseError: function parseError(str, hash) {
	      if (hash.recoverable) {
	        this.trace(str);
	      } else {
	        var error = new Error(str);
	        error.hash = hash;
	        throw error;
	      }
	    },
	    parse: function parse(input) {
	      var self = this,
	          stack = [0],
	          vstack = [null],
	          lstack = [],
	          table = this.table,
	          yytext = '',
	          yylineno = 0,
	          yyleng = 0,
	          TERROR = 2,
	          EOF = 1;
	      var args = lstack.slice.call(arguments, 1);
	      var lexer = Object.create(this.lexer);
	      var sharedState = {
	        yy: {}
	      };

	      for (var k in this.yy) {
	        if (Object.prototype.hasOwnProperty.call(this.yy, k)) {
	          sharedState.yy[k] = this.yy[k];
	        }
	      }

	      lexer.setInput(input, sharedState.yy);
	      sharedState.yy.lexer = lexer;
	      sharedState.yy.parser = this;

	      if (typeof lexer.yylloc == 'undefined') {
	        lexer.yylloc = {};
	      }

	      var yyloc = lexer.yylloc;
	      lstack.push(yyloc);
	      var ranges = lexer.options && lexer.options.ranges;

	      if (typeof sharedState.yy.parseError === 'function') {
	        this.parseError = sharedState.yy.parseError;
	      } else {
	        this.parseError = Object.getPrototypeOf(this).parseError;
	      }

	       var lex = function lex() {
	        var token;
	        token = lexer.lex() || EOF;

	        if (typeof token !== 'number') {
	          token = self.symbols_[token] || token;
	        }

	        return token;
	      };

	      var symbol,
	          preErrorSymbol,
	          state,
	          action,
	          r,
	          yyval = {},
	          p,
	          len,
	          newState,
	          expected;

	      while (true) {
	        state = stack[stack.length - 1];

	        if (this.defaultActions[state]) {
	          action = this.defaultActions[state];
	        } else {
	          if (symbol === null || typeof symbol == 'undefined') {
	            symbol = lex();
	          }

	          action = table[state] && table[state][symbol];
	        }

	        if (typeof action === 'undefined' || !action.length || !action[0]) {
	          var errStr = '';
	          expected = [];

	          for (p in table[state]) {
	            if (this.terminals_[p] && p > TERROR) {
	              expected.push('\'' + this.terminals_[p] + '\'');
	            }
	          }

	          if (lexer.showPosition) {
	            errStr = 'Parse error on line ' + (yylineno + 1) + ':\n' + lexer.showPosition() + '\nExpecting ' + expected.join(', ') + ', got \'' + (this.terminals_[symbol] || symbol) + '\'';
	          } else {
	            errStr = 'Parse error on line ' + (yylineno + 1) + ': Unexpected ' + (symbol == EOF ? 'end of input' : '\'' + (this.terminals_[symbol] || symbol) + '\'');
	          }

	          this.parseError(errStr, {
	            text: lexer.match,
	            token: this.terminals_[symbol] || symbol,
	            line: lexer.yylineno,
	            loc: yyloc,
	            expected: expected
	          });
	        }

	        if (action[0] instanceof Array && action.length > 1) {
	          throw new Error('Parse Error: multiple actions possible at state: ' + state + ', token: ' + symbol);
	        }

	        switch (action[0]) {
	          case 1:
	            stack.push(symbol);
	            vstack.push(lexer.yytext);
	            lstack.push(lexer.yylloc);
	            stack.push(action[1]);
	            symbol = null;

	            if (!preErrorSymbol) {
	              yyleng = lexer.yyleng;
	              yytext = lexer.yytext;
	              yylineno = lexer.yylineno;
	              yyloc = lexer.yylloc;
	            } else {
	              symbol = preErrorSymbol;
	              preErrorSymbol = null;
	            }

	            break;

	          case 2:
	            len = this.productions_[action[1]][1];
	            yyval.$ = vstack[vstack.length - len];
	            yyval._$ = {
	              first_line: lstack[lstack.length - (len || 1)].first_line,
	              last_line: lstack[lstack.length - 1].last_line,
	              first_column: lstack[lstack.length - (len || 1)].first_column,
	              last_column: lstack[lstack.length - 1].last_column
	            };

	            if (ranges) {
	              yyval._$.range = [lstack[lstack.length - (len || 1)].range[0], lstack[lstack.length - 1].range[1]];
	            }

	            r = this.performAction.apply(yyval, [yytext, yyleng, yylineno, sharedState.yy, action[1], vstack, lstack].concat(args));

	            if (typeof r !== 'undefined') {
	              return r;
	            }

	            if (len) {
	              stack = stack.slice(0, -1 * len * 2);
	              vstack = vstack.slice(0, -1 * len);
	              lstack = lstack.slice(0, -1 * len);
	            }

	            stack.push(this.productions_[action[1]][0]);
	            vstack.push(yyval.$);
	            lstack.push(yyval._$);
	            newState = table[stack[stack.length - 2]][stack[stack.length - 1]];
	            stack.push(newState);
	            break;

	          case 3:
	            return true;
	        }
	      }

	      return true;
	    }
	  };

	  function Parser() {
	    this.yy = {};
	  }

	  Parser.prototype = parser;
	  parser.Parser = Parser;
	  return new Parser();
	}();

	var parser_1 = parser;
	var compiled_parser = {
	  parser: parser_1
	};

	var nodes = createCommonjsModule(function (module, exports) {
	  function indent(str) {
	    return function () {
	      var ref = str.split('\n');
	      var results = [];

	      for (var i = 0, len = ref.length; i < len; i++) {
	        results.push("  " + ref[i]);
	      }

	      return results;
	    }().join('\n');
	  }

	  exports.Select =
	  /*#__PURE__*/
	  function () {
	    function Select(fields, source, distinct, joins, unions) {
	      if (distinct === void 0) {
	        distinct = false;
	      }

	      if (joins === void 0) {
	        joins = [];
	      }

	      if (unions === void 0) {
	        unions = [];
	      }

	      this.fields = fields;
	      this.source = source;
	      this.distinct = distinct;
	      this.joins = joins;
	      this.unions = unions;
	      this.order = null;
	      this.group = null;
	      this.where = null;
	      this.limit = null;
	    }

	    var _proto = Select.prototype;

	    _proto.toString = function toString() {
	      var ret = ["SELECT " + this.fields.join(', ')];
	      ret.push(indent("FROM " + this.source));

	      for (var i = 0, len = this.joins.length; i < len; i++) {
	        ret.push(indent(this.joins[i].toString()));
	      }

	      if (this.where) {
	        ret.push(indent(this.where.toString()));
	      }

	      if (this.group) {
	        ret.push(indent(this.group.toString()));
	      }

	      if (this.order) {
	        ret.push(indent(this.order.toString()));
	      }

	      if (this.limit) {
	        ret.push(indent(this.limit.toString()));
	      }

	      for (var j = 0, len1 = this.unions.length; j < len1; j++) {
	        ret.push(this.unions[j].toString());
	      }

	      return ret.join('\n');
	    };

	    return Select;
	  }();

	  exports.SubSelect =
	  /*#__PURE__*/
	  function () {
	    function SubSelect(select, name) {
	      if (name === void 0) {
	        name = null;
	      }

	      this.select = select;
	      this.name = name;
	    }

	    var _proto2 = SubSelect.prototype;

	    _proto2.toString = function toString() {
	      var ret = [];
	      ret.push('(');
	      ret.push(indent(this.select.toString()));
	      ret.push(this.name ? ") " + this.name.toString() : ')');
	      return ret.join('\n');
	    };

	    return SubSelect;
	  }();

	  exports.Join =
	  /*#__PURE__*/
	  function () {
	    function Join(right, conditions, side, mode) {
	      if (conditions === void 0) {
	        conditions = null;
	      }

	      if (side === void 0) {
	        side = null;
	      }

	      if (mode === void 0) {
	        mode = null;
	      }

	      this.right = right;
	      this.conditions = conditions;
	      this.side = side;
	      this.mode = mode;
	    }

	    var _proto3 = Join.prototype;

	    _proto3.toString = function toString() {
	      var ret = '';

	      if (this.side != null) {
	        ret += this.side + " ";
	      }

	      if (this.mode != null) {
	        ret += this.mode + " ";
	      }

	      return ret + ("JOIN " + this.right + "\n") + indent("ON " + this.conditions);
	    };

	    return Join;
	  }();

	  exports.Union =
	  /*#__PURE__*/
	  function () {
	    function Union(query, all1) {
	      if (all1 === void 0) {
	        all1 = false;
	      }

	      this.query = query;
	      this.all = all1;
	    }

	    var _proto4 = Union.prototype;

	    _proto4.toString = function toString() {
	      var all = this.all ? ' ALL' : '';
	      return "UNION" + all + "\n" + this.query.toString();
	    };

	    return Union;
	  }();

	  exports.LiteralValue =
	  /*#__PURE__*/
	  function () {
	    function LiteralValue(value1, value2) {
	      if (value2 === void 0) {
	        value2 = null;
	      }

	      this.value = value1;
	      this.value2 = value2;

	      if (this.value2) {
	        this.nested = true;
	        this.values = this.value.values;
	        this.values.push(this.value2);
	      } else {
	        this.nested = false;
	        this.values = [this.value];
	      }
	    } // TODO: Backtick quotes only supports MySQL, Postgres uses double-quotes


	    var _proto5 = LiteralValue.prototype;

	    _proto5.toString = function toString(quote) {
	      if (quote === void 0) {
	        quote = true;
	      }

	      if (quote) {
	        return "`" + this.values.join('`.`') + "`";
	      } else {
	        return "" + this.values.join('.');
	      }
	    };

	    return LiteralValue;
	  }();

	  exports.StringValue =
	  /*#__PURE__*/
	  function () {
	    function StringValue(value1, quoteType) {
	      if (quoteType === void 0) {
	        quoteType = '\'\'';
	      }

	      this.value = value1;
	      this.quoteType = quoteType;
	    }

	    var _proto6 = StringValue.prototype;

	    _proto6.toString = function toString() {
	      var escaped = this.quoteType === '\'' ? this.value.replace(/(^|[^\\])'/g, '$1\'\'') : this.value;
	      return "" + this.quoteType + escaped + this.quoteType;
	    };

	    return StringValue;
	  }();

	  exports.NumberValue =
	  /*#__PURE__*/
	  function () {
	    function NumberValue(value) {
	      this.value = Number(value);
	    }

	    var _proto7 = NumberValue.prototype;

	    _proto7.toString = function toString() {
	      return this.value.toString();
	    };

	    return NumberValue;
	  }();

	  exports.ListValue =
	  /*#__PURE__*/
	  function () {
	    function ListValue(value1) {
	      this.value = value1;
	    }

	    var _proto8 = ListValue.prototype;

	    _proto8.toString = function toString() {
	      return "(" + this.value.join(', ') + ")";
	    };

	    return ListValue;
	  }();

	  exports.WhitepaceList =
	  /*#__PURE__*/
	  function () {
	    function WhitepaceList(value1) {
	      this.value = value1;
	    }

	    var _proto9 = WhitepaceList.prototype;

	    _proto9.toString = function toString() {
	      // not backtick for literals
	      return this.value.map(function (value) {
	        if (value instanceof exports.LiteralValue) {
	          return value.toString(false);
	        } else {
	          return value.toString();
	        }
	      }).join(' ');
	    };

	    return WhitepaceList;
	  }();

	  exports.ParameterValue =
	  /*#__PURE__*/
	  function () {
	    function ParameterValue(value) {
	      this.value = value;
	      this.index = parseInt(value.substr(1), 10) - 1;
	    }

	    var _proto10 = ParameterValue.prototype;

	    _proto10.toString = function toString() {
	      return "$" + this.value;
	    };

	    return ParameterValue;
	  }();

	  exports.ArgumentListValue =
	  /*#__PURE__*/
	  function () {
	    function ArgumentListValue(value1, distinct) {
	      if (distinct === void 0) {
	        distinct = false;
	      }

	      this.value = value1;
	      this.distinct = distinct;
	    }

	    var _proto11 = ArgumentListValue.prototype;

	    _proto11.toString = function toString() {
	      if (this.distinct) {
	        return "DISTINCT " + this.value.join(', ');
	      } else {
	        return "" + this.value.join(', ');
	      }
	    };

	    return ArgumentListValue;
	  }();

	  exports.BooleanValue =
	  /*#__PURE__*/
	  function () {
	    function LiteralValue(value) {
	      this.value = function () {
	        switch (value.toLowerCase()) {
	          case 'true':
	            return true;

	          case 'false':
	            return false;

	          default:
	            return null;
	        }
	      }();
	    }

	    var _proto12 = LiteralValue.prototype;

	    _proto12.toString = function toString() {
	      if (this.value != null) {
	        return this.value.toString().toUpperCase();
	      } else {
	        return 'NULL';
	      }
	    };

	    return LiteralValue;
	  }();

	  exports.FunctionValue =
	  /*#__PURE__*/
	  function () {
	    function FunctionValue(name, _arguments, udf) {
	      if (_arguments === void 0) {
	        _arguments = null;
	      }

	      if (udf === void 0) {
	        udf = false;
	      }

	      this.name = name;
	      this.arguments = _arguments;
	      this.udf = udf;
	    }

	    var _proto13 = FunctionValue.prototype;

	    _proto13.toString = function toString() {
	      if (this.arguments) {
	        return this.name.toUpperCase() + "(" + this.arguments.toString() + ")";
	      } else {
	        return this.name.toUpperCase() + "()";
	      }
	    };

	    return FunctionValue;
	  }();

	  exports.Case =
	  /*#__PURE__*/
	  function () {
	    function Case(whens, _else) {
	      this.whens = whens;
	      this["else"] = _else;
	    }

	    var _proto14 = Case.prototype;

	    _proto14.toString = function toString() {
	      var whensStr = this.whens.map(function (w) {
	        return w.toString();
	      }).join(' ');

	      if (this["else"]) {
	        return "CASE " + whensStr + " " + this["else"].toString() + " END";
	      } else {
	        return "CASE " + whensStr + " END";
	      }
	    };

	    return Case;
	  }();

	  exports.CaseWhen =
	  /*#__PURE__*/
	  function () {
	    function CaseWhen(whenCondition, resCondition) {
	      this.whenCondition = whenCondition;
	      this.resCondition = resCondition;
	    }

	    var _proto15 = CaseWhen.prototype;

	    _proto15.toString = function toString() {
	      return "WHEN " + this.whenCondition + " THEN " + this.resCondition;
	    };

	    return CaseWhen;
	  }();

	  exports.CaseElse =
	  /*#__PURE__*/
	  function () {
	    function CaseElse(elseCondition) {
	      this.elseCondition = elseCondition;
	    }

	    var _proto16 = CaseElse.prototype;

	    _proto16.toString = function toString() {
	      return "ELSE " + this.elseCondition;
	    };

	    return CaseElse;
	  }();

	  exports.Order =
	  /*#__PURE__*/
	  function () {
	    function Order(orderings, offset) {
	      this.orderings = orderings;
	      this.offset = offset;
	    }

	    var _proto17 = Order.prototype;

	    _proto17.toString = function toString() {
	      return "ORDER BY " + this.orderings.join(', ') + (this.offset ? '\n' + this.offset.toString() : '');
	    };

	    return Order;
	  }();

	  exports.OrderArgument =
	  /*#__PURE__*/
	  function () {
	    function OrderArgument(value, direction) {
	      if (direction === void 0) {
	        direction = 'ASC';
	      }

	      this.value = value;
	      this.direction = direction;
	    }

	    var _proto18 = OrderArgument.prototype;

	    _proto18.toString = function toString() {
	      return this.value + " " + this.direction;
	    };

	    return OrderArgument;
	  }();

	  exports.Offset =
	  /*#__PURE__*/
	  function () {
	    function Offset(row_count, limit) {
	      this.row_count = row_count;
	      this.limit = limit;
	    }

	    var _proto19 = Offset.prototype;

	    _proto19.toString = function toString() {
	      return "OFFSET " + this.row_count + " ROWS" + (this.limit ? "\nFETCH NEXT " + this.limit + " ROWS ONLY" : '');
	    };

	    return Offset;
	  }();

	  exports.Limit =
	  /*#__PURE__*/
	  function () {
	    function Limit(value1, offset) {
	      this.value = value1;
	      this.offset = offset;
	    }

	    var _proto20 = Limit.prototype;

	    _proto20.toString = function toString() {
	      return "LIMIT " + this.value + (this.offset ? "\nOFFSET " + this.offset : '');
	    };

	    return Limit;
	  }();

	  exports.Table =
	  /*#__PURE__*/
	  function () {
	    function Table(name, alias, win, winFn, winArg) {
	      if (alias === void 0) {
	        alias = null;
	      }

	      if (win === void 0) {
	        win = null;
	      }

	      if (winFn === void 0) {
	        winFn = null;
	      }

	      if (winArg === void 0) {
	        winArg = null;
	      }

	      this.name = name;
	      this.alias = alias;
	      this.win = win;
	      this.winFn = winFn;
	      this.winArg = winArg;
	    }

	    var _proto21 = Table.prototype;

	    _proto21.toString = function toString() {
	      if (this.win) {
	        return this.name + "." + this.win + ":" + this.winFn + "(" + this.winArg + ")";
	      } else if (this.alias) {
	        return this.name + " AS " + this.alias;
	      } else {
	        return this.name.toString();
	      }
	    };

	    return Table;
	  }();

	  exports.Group =
	  /*#__PURE__*/
	  function () {
	    function Group(fields) {
	      this.fields = fields;
	      this.having = null;
	    }

	    var _proto22 = Group.prototype;

	    _proto22.toString = function toString() {
	      var ret = ["GROUP BY " + this.fields.join(', ')];

	      if (this.having) {
	        ret.push(this.having.toString());
	      }

	      return ret.join('\n');
	    };

	    return Group;
	  }();

	  exports.Where =
	  /*#__PURE__*/
	  function () {
	    function Where(conditions) {
	      this.conditions = conditions;
	    }

	    var _proto23 = Where.prototype;

	    _proto23.toString = function toString() {
	      return "WHERE " + this.conditions;
	    };

	    return Where;
	  }();

	  exports.Having =
	  /*#__PURE__*/
	  function () {
	    function Having(conditions) {
	      this.conditions = conditions;
	    }

	    var _proto24 = Having.prototype;

	    _proto24.toString = function toString() {
	      return "HAVING " + this.conditions;
	    };

	    return Having;
	  }();

	  exports.Op =
	  /*#__PURE__*/
	  function () {
	    function Op(operation, left, right) {
	      this.operation = operation;
	      this.left = left;
	      this.right = right;
	    }

	    var _proto25 = Op.prototype;

	    _proto25.toString = function toString() {
	      return "(" + this.left + " " + this.operation.toUpperCase() + " " + this.right + ")";
	    };

	    return Op;
	  }();

	  exports.UnaryOp =
	  /*#__PURE__*/
	  function () {
	    function UnaryOp(operator, operand) {
	      this.operator = operator;
	      this.operand = operand;
	    }

	    var _proto26 = UnaryOp.prototype;

	    _proto26.toString = function toString() {
	      return "(" + this.operator.toUpperCase() + " " + this.operand + ")";
	    };

	    return UnaryOp;
	  }();

	  exports.BetweenOp =
	  /*#__PURE__*/
	  function () {
	    function BetweenOp(value) {
	      this.value = value;
	    }

	    var _proto27 = BetweenOp.prototype;

	    _proto27.toString = function toString() {
	      return "" + this.value.join(' AND ');
	    };

	    return BetweenOp;
	  }();

	  exports.Field =
	  /*#__PURE__*/
	  function () {
	    function Field(field, name) {
	      if (name === void 0) {
	        name = null;
	      }

	      this.field = field;
	      this.name = name;
	    }

	    var _proto28 = Field.prototype;

	    _proto28.toString = function toString() {
	      if (this.name) {
	        return this.field + " AS " + this.name;
	      } else {
	        return this.field.toString();
	      }
	    };

	    return Field;
	  }();

	  exports.Star =
	  /*#__PURE__*/
	  function () {
	    function Star() {}

	    var _proto29 = Star.prototype;

	    _proto29.toString = function toString() {
	      return '*';
	    };

	    return Star;
	  }();
	});
	var nodes_1 = nodes.Select;
	var nodes_2 = nodes.SubSelect;
	var nodes_3 = nodes.Join;
	var nodes_4 = nodes.Union;
	var nodes_5 = nodes.LiteralValue;
	var nodes_6 = nodes.StringValue;
	var nodes_7 = nodes.NumberValue;
	var nodes_8 = nodes.ListValue;
	var nodes_9 = nodes.WhitepaceList;
	var nodes_10 = nodes.ParameterValue;
	var nodes_11 = nodes.ArgumentListValue;
	var nodes_12 = nodes.BooleanValue;
	var nodes_13 = nodes.FunctionValue;
	var nodes_14 = nodes.Case;
	var nodes_15 = nodes.CaseWhen;
	var nodes_16 = nodes.CaseElse;
	var nodes_17 = nodes.Order;
	var nodes_18 = nodes.OrderArgument;
	var nodes_19 = nodes.Offset;
	var nodes_20 = nodes.Limit;
	var nodes_21 = nodes.Table;
	var nodes_22 = nodes.Group;
	var nodes_23 = nodes.Where;
	var nodes_24 = nodes.Having;
	var nodes_25 = nodes.Op;
	var nodes_26 = nodes.UnaryOp;
	var nodes_27 = nodes.BetweenOp;
	var nodes_28 = nodes.Field;
	var nodes_29 = nodes.Star;

	var parser$1 = compiled_parser.parser;
	parser$1.lexer = {
	  lex: function lex() {
	    var tag;

	    var _ref = this.tokens[this.pos++] || [''];

	    tag = _ref[0];
	    this.yytext = _ref[1];
	    this.yylineno = _ref[2];
	    return tag;
	  },
	  setInput: function setInput(tokens) {
	    this.tokens = tokens;
	    return this.pos = 0;
	  },
	  upcomingInput: function upcomingInput() {
	    return '';
	  }
	};
	parser$1.yy = nodes;
	var parser_2 = parser$1;

	var parse = function parse(str) {
	  return parser$1.parse(str);
	};

	var parser_1$1 = {
	  parser: parser_2,
	  parse: parse
	};

	var sql_parser = createCommonjsModule(function (module, exports) {
	  exports.lexer = lexer;
	  exports.parser = parser_1$1;
	  exports.nodes = nodes;

	  exports.parse = function (sql) {
	    return exports.parser.parse(exports.lexer.tokenize(sql));
	  };
	});
	var sql_parser_1 = sql_parser.lexer;
	var sql_parser_2 = sql_parser.parser;
	var sql_parser_3 = sql_parser.nodes;
	var sql_parser_4 = sql_parser.parse;

	exports.default = sql_parser;
	exports.lexer = sql_parser_1;
	exports.nodes = sql_parser_3;
	exports.parse = sql_parser_4;
	exports.parser = sql_parser_2;

	Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=sql-parser.js.map
