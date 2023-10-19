<?php

namespace common\classes;

trait TlErrorHandlerTrait
{
    public function handleError($code, $message, $file, $line)
    {
        if (error_reporting() & $code) { // also for suppress operator @
            if (PHP_VERSION_ID >= 80000) {
                if (YII_ENV=='prod' || (defined('PHP8WARN_OFF') && PHP8WARN_OFF)) {
                    if ($code == E_WARNING && (
                            preg_match('/^(Attempt to read property|Undefined property|Undefined variable|Undefined array key)/', $message) ||
                            preg_match('/^Trying to access array offset on value of type (null|bool|int)/', $message) ||
                            preg_match('/^Constant [\w_]* already defined/', $message)
                        )) {
                        \Yii::warning("$message at $file:$line", 'PHP8Warning');
                        return true;
                    }
                    if (PHP_VERSION_ID >= 80200) {
                        if ($code == E_DEPRECATED && (
                                preg_match('/^Creation of dynamic property .* is deprecated/', $message) ||
                                str_starts_with($message, 'Use of "self" in callables is deprecated')
                            )) {
                            \Yii::warning("$message at $file:$line", 'PHP82Warning');
                        }
                    }
                    if ($code == E_DEPRECATED) {
                        return true;
                    }

                } else { // development

                    if (PHP_VERSION_ID >= 80200) {
                        if ($code == E_DEPRECATED) {
                            if (str_starts_with($message, 'Using ${var} in strings is deprecated, use {$var} instead') ||
                                str_starts_with($message, 'Using ${expr} (variable variables) in strings is deprecated, use {${expr}} instead') ||
                                str_starts_with($message, 'Use of "self" in callables is deprecated') ||
                                str_starts_with($message, 'Function utf8_decode() is deprecated') ||
                                str_starts_with($message, 'Function utf8_encode() is deprecated') ||
                                preg_match('/^Creation of dynamic property .* is deprecated/', $message)
                            ) {
                                \Yii::warning($message, 'PHP82Warning');
                                return true;
                            }
                        }
                    }
                        // suppress for php 8.1
                    if (PHP_VERSION_ID >= 80100) {
                        if ($code == E_DEPRECATED) {
                            if (preg_match('/^[_\w\d]*\(\): Passing null to parameter #\d/', $message, $match)) {
                                if (!in_array($match[1], explode(' ', 'abs strpos stripos strrpos mb_strrpos strlen str_replace substr parse_str addslashes strtotime trim basename stripslashes defined round number_format explode class_exists urldecode json_decode html_entity_decode preg_replace')) ) {
                                    \Yii::warning($message, 'PHP81Warning');
                                }
                                return true;
                            }
                            if (str_starts_with($message, 'Function strftime() is deprecated') ||
                                str_starts_with($message, 'Constant FILTER_SANITIZE_STRING is deprecated') ||
                                str_starts_with($message, 'Automatic conversion of false to array is deprecated') ||
                                str_starts_with($message, 'auto_detect_line_endings is deprecated')
                            ) {
                                return true;
                            }

                            if (preg_match('/([\w\\\\]*) implements the Serializable interface, which is deprecated/', $message, $match)) {
                                if (str_contains($match[1], 'Opis\Closure\SerializableClosure')) {
//                                    \Yii::warning($message, 'PHP81WarningOPIS');
                                    return true;
                                } else {
                                    \Yii::warning($message, 'PHP81Warning');
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return parent::handleError($code, $message, $file, $line);
    }
}