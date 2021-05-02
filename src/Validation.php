<?php


namespace Sparrow;


class Validation
{

    /**
     * @var array
     */

    protected array $messages = [];

    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value, array $messages = [])
    {
        $this->value = $value;
    }

    /**
     * Returns false when value is smaller than the parameter
     * @param int  $min
     * @return bool
     */

    public function min(int $min): bool
    {
        if (is_int($this->value))
            $value = $this->value;
        elseif (is_string($this->value))
            $value = strlen($this->value);
        elseif (is_array($this->value))
            $value = count($this->value);
        else
            return false;

        return $min < $value;
    }

    /**
     * Returns false when value is bigger than $max
     * @param int $max
     * @return bool
     */

    public function max(int $max): bool
    {
        if (is_int($this->value))
            $value = $this->value;
        elseif (is_string($this->value))
            $value = strlen($this->value);
        elseif (is_array($this->value))
            $value = count($this->value);
        else
            return false;

        return $max > $value;
    }

    /**
     * Checks if value is equals to $equals or not
     * @param mixed $equals
     * @return bool
     */

    public function equals($equals): bool
    {
        return $equals == $this->value;
    }

    /**
     * Checks if input is email or not
     * @return bool
     */

    public function email(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Checks if string length is equal to the $length
     * @param int $length
     * @return bool
     */

    public function length(int $length): bool
    {
        return $length == mb_strlen($this->value);
    }

    /**
     * @param string $regex
     * @return false|int
     */

    public function pregCheck(string $regex) {
        return preg_match($regex, $this->value);
    }

    /**
     * @return bool
     */

    public function numeric(): bool
    {
        return is_numeric($this->value);
    }

    /**
     * @return bool
     */

    public function int(): bool
    {
        return is_int($this->value);
    }

    /**
     * @return bool
     */

    public function string(): bool
    {
        return is_string($this->value);
    }

    /**
     * @return bool
     */

    public function _array(): bool
    {
        return is_array($this->value);
    }

    /**
     * @param $equals
     * @return bool
     */

    public function notEqual($equals): bool
    {
        return $equals != $this->value;
    }

    /**
     * @param int $count
     * @return bool
     */

    public function count(int $count): bool
    {
        return $count == count($this->value);
    }

    /**
     * @param array $items
     * @param string $method
     * @return array|bool
     */

    public function validate(array $items, string $method = 'post') {
        foreach ($items as $index => $item) {
            $key = explode(':', $item);
            if ($method == 'post')
                $this->value = Post::get($index);
            else
                $this->value = $_GET[$index];
            $validationRule = $key[0];
            $validate = $this->{$validationRule}($key[1] ?? null);
            if (!$validate)
                $messages[] = $this->messages[$index . '.' . $key[0]];
        }
        return $messages ?? true;
    }
}