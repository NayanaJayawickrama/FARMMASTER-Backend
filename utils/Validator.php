<?php

class Validator {
    
    public static function required($value, $field) {
        // Handle arrays differently than strings
        if (is_array($value)) {
            if (empty($value)) {
                throw new Exception("{$field} is required.");
            }
            return $value;
        }
        
        // For strings, trim and check
        if (empty(trim($value))) {
            throw new Exception("{$field} is required.");
        }
        return trim($value);
    }

    public static function email($email) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (empty($email)) {
            throw new Exception("Email is required.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }
        
        return $email;
    }

    public static function name($name, $fieldName) {
        if (empty(trim($name))) {
            throw new Exception("{$fieldName} is required.");
        }
        
        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            throw new Exception("{$fieldName} should contain only letters and spaces.");
        }
        
        return trim($name);
    }

    public static function password($password) {
        if (empty($password)) {
            throw new Exception("Password is required.");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long.");
        }
        
        if (!preg_match("/^(?=.*[a-zA-Z])(?=.*\d)/", $password)) {
            throw new Exception("Password must contain at least one letter and one number.");
        }
        
        return $password;
    }

    public static function phone($phone) {
        if (empty($phone)) {
            return null; // Phone is optional
        }
        
        $phone = trim($phone);
        
        // Remove any spaces, dashes, or parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Handle different phone number formats
        if (strpos($phone, '+') === 0) {
            // International format with +
            if (strpos($phone, '+94') === 0) {
                // Sri Lankan number with +94
                $digits = substr($phone, 3);
                if (!preg_match("/^\d{9}$/", $digits)) {
                    throw new Exception("Sri Lankan phone number must have 9 digits after +94.");
                }
                return $phone;
            } else {
                // Other international number
                if (preg_match("/^\+\d{10,15}$/", $phone)) {
                    return $phone;
                } else {
                    throw new Exception("Invalid international phone number format.");
                }
            }
        } elseif (preg_match("/^\d+$/", $phone)) {
            // All digits, determine format
            $len = strlen($phone);
            
            if ($len === 9) {
                // 9 digits - assume Sri Lankan mobile without country code
                return '+94' . $phone;
            } elseif ($len === 10) {
                // 10 digits - could be Sri Lankan with leading 0
                if (substr($phone, 0, 1) === '0') {
                    return '+94' . substr($phone, 1);
                } else {
                    // 10 digits without leading 0, treat as international
                    return $phone;
                }
            } elseif ($len >= 10 && $len <= 15) {
                // 10-15 digits, accept as is
                return $phone;
            } else {
                throw new Exception("Phone number must be 9-15 digits.");
            }
        } else {
            throw new Exception("Phone number can only contain digits, spaces, dashes, and parentheses.");
        }
    }

    public static function numeric($value, $fieldName, $min = null, $max = null) {
        if (!is_numeric($value)) {
            throw new Exception("{$fieldName} must be a number.");
        }

        $value = floatval($value);

        if ($min !== null && $value < $min) {
            throw new Exception("{$fieldName} must be at least {$min}.");
        }

        if ($max !== null && $value > $max) {
            throw new Exception("{$fieldName} must not exceed {$max}.");
        }

        return $value;
    }

    public static function inArray($value, $array, $fieldName) {
        if (!in_array($value, $array)) {
            throw new Exception("Invalid {$fieldName} value.");
        }
        return $value;
    }

    public static function minLength($value, $min, $fieldName) {
        if (strlen(trim($value)) < $min) {
            throw new Exception("{$fieldName} must be at least {$min} characters long.");
        }
        return trim($value);
    }

    public static function maxLength($value, $max, $fieldName) {
        if (strlen(trim($value)) > $max) {
            throw new Exception("{$fieldName} must not exceed {$max} characters.");
        }
        return trim($value);
    }

    public static function sanitizeString($value) {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    public static function positiveNumber($value, $fieldName) {
        $value = trim($value);
        
        if (empty($value) || !is_numeric($value)) {
            throw new Exception("{$fieldName} must be a valid number.");
        }
        
        $numValue = floatval($value);
        
        if ($numValue < 0) {
            throw new Exception("{$fieldName} must be a positive number.");
        }
        
        return $numValue;
    }

    public static function date($value, $fieldName) {
        if (empty(trim($value))) {
            throw new Exception("{$fieldName} is required.");
        }
        
        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new Exception("{$fieldName} must be a valid date in YYYY-MM-DD format.");
        }
        
        return $value;
    }
}

?>