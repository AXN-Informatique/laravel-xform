<?php

namespace Axn\LaravelXform;

use Illuminate\Html\FormBuilder as BaseFormBuilder;

class FormBuilder extends BaseFormBuilder
{
    protected $prefix;

    protected $current_value;

    /**
     * Open up a new HTML form.
     *
     * @param  array   $options
     * @return string
     */
    public function open(array $options = array())
    {
        if ( ! empty($options['prefix'])) {
            $this->prefix = $options['prefix'];
            array_forget($options, ['prefix']);
        }
        return parent::open($options);
    }

    /**
     * Get the value that should be assigned to the field.
     *
     * @param  string  $name
     * @param  string  $value
     * @return string
     */
    public function getValueAttribute($name, $value = null)
    {
        if (is_null($name))
        {
            $this->current_value = $value;
            return $this->current_value;
        }

        if ( ! is_null($this->old($name)))
        {
            $this->current_value = $this->old($name);;
            return $this->current_value;
        }

        if ( ! is_null($value))
        {
            $this->current_value = $value;
            return $this->current_value;
        }

        if (isset($this->model))
        {
            $this->current_value = $this->getModelValueAttribute(str_replace($this->prefix, "", $name));
            return $this->current_value;
        }
    }

    public function getCurrentValue()
    {
        return $this->current_value;
    }
}
