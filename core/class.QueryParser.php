<?php
/**
 * Query Parser
 *
 * HJQuery is a format query string in Haojing
 * like: (a:b OR a:"c" OR (b:[1,10] AND e:[,100]))
 */
class QueryParser {
	public static function parse($HJQueryString) {
		$str = preg_replace('/\s+/', ' ', trim($HJQueryString));

		$RPNStack = self::buildRPNStack($str);

		$opStack = [];
		foreach ($RPNStack as $item) {
			if (is_array($item)) {
				if (!$item['key']) $item['key'] = 'content'; //use the full indexed content field;
				if (preg_match('/^\[(?<lower>\d*),(?<upper>\d*)\]$/', $item['value'], $m)) {
					$query = new RangeQuery($item['key'], $m['lower'] == '' ? null : $m['lower'], $m['upper'] == '' ? null : $m['upper']);
				} elseif (preg_match('/^\[(?<lower>\d+|\*) TO (?<upper>\d+|\*)\]$/', $item['value'], $m)) {
					$query = new RangeQuery($item['key'], $m['lower'] == '*' ? null : $m['lower'], $m['upper'] == '*' ? null : $m['upper']);
				} elseif (preg_match('/^\{(([\"\']?[^\"\'\,]+[\"\'\,]*)+)\}$/', $item['value'], $m)) {
					$values = array_map(function ($o) {return trim(preg_replace('/^(\"|\')(.*?)\1$/', "$2", $o));}, explode(',', $m[1]));
					$query = new InQuery($item['key'], $values);
				} elseif ($item['value'] == '*') {
					$query = new ExistQuery($item['key']);
				} else {
					$query = new Query($item['key'], $item['value']);
				}
				array_push($opStack, $query);
				continue;
			}
			switch ($item) {
				case 'AND':
				case 'OR':
					$queryClass = ucfirst(strtolower($item)) . 'Query';
					array_push($opStack, new $queryClass(array_pop($opStack), array_pop($opStack)));
					break;
				case 'NOT':
					array_push($opStack, new NotQuery(array_pop($opStack)));
					break;
				default:
					throw new Exception('Invalid Expression!');
			}
		}

		$query = array_pop($opStack);
		//var_dump($opStack);
		if (count($opStack)) throw new Exception('Invalid Expression!');
		//var_dump($query);
		return $query;
	}

	private static function buildRPNStack($str) {
		//RPN = Reverse Polish Notation, refer: http://blog.kingsamchen.com/archives/637
		$RPNStack = $opStack = [];
		$opPriority = ['(' => 9, ')' => 9, 'NOT' => 3, 'AND' => 2, 'OR' => 1];
		for ($i = 0, $len = mb_strlen($str); $i < $len; $i++) {
			$currentStr = mb_substr($str, $i);
			if ($currentStr[0] == ' ') {
				if (preg_match('/^\s(AND\s?|&&\s?|OR\s?|\|\|\s?)/i', $currentStr, $m)) continue;
				$i -= 3;
				$currentStr = 'AND' . $currentStr;
			}

			if (preg_match('/^(?<op>AND\s?|&&\s?|OR\s?|\|\|\s?|NOT\s?|\-\s?|\(|\))/i', $currentStr, $m)) {
				$i += (mb_strlen($m[1]) - 1);
				//var_dump($m['op']);
				$m['op'] = strtoupper(str_replace(array('&&', '||', '-'), array('AND', 'OR', 'NOT'), trim($m['op'])));
				switch ($m['op']) {
					case 'NOT':
						array_push($opStack, $m['op']);
						break;
					case '(':
						array_push($opStack, $m['op']);
						break;
					case ')':
						do {
							$RPNStack[] = $op = array_pop($opStack);
							if ($op == '(') array_pop($RPNStack);
						} while ($op != '(');
						break;
					default:
						while (!(count($opStack) == 0 || end($opStack) == '(' || $opPriority[$m['op']] >= $opPriority[end($opStack)])) {
							$RPNStack[] = array_pop($opStack);
						}
						array_push($opStack, $m['op']);
				}
			} elseif (preg_match('/^(((?<key>[^\:\(\)]+)\s*\:)?\s*([\"\'](?<value>[^\"\'\(\)]+)[\"\']|(?<value2>(\[[^\]]+\]|\{[^\}]+\}))|(?<value3>[^\s\(\)]+))).*$/i', $currentStr, $m)) {
				//var_dump($m);
				/*
				* value : a:"aa bb OR cc" 引号内部的数据作为一个独立个体处理
				*
				* value2 :  闭区间: a:[1,4]   InQuery: a:{1,2,3}  不支持半开半闭或者开区间写法
				*
				* value3 : 普通参数，不带空格
				* */
				$RPNStack[] = [
						'key' => $m['key'],
						'value' => isset($m['value3']) ? $m['value3']
						: (isset($m['value2']) ? $m['value2'] : $m['value'])
				];
				$i += (mb_strlen($m[1]) - 1);
			}
		}

		if (count($opStack)) $RPNStack = array_merge($RPNStack, array_reverse($opStack));
		return $RPNStack;
	}
}
 
/*----- END FILE: class.QueryParser.php -----*/