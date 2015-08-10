<?php

namespace Axn\LaravelXForm;

use Collective\Html\HtmlBuilder;
use Collective\Html\FormBuilder;
use Illuminate\Config\Repository as Config;
use Illuminate\Session\SessionManager as Session;
use Illuminate\Support\Str;

use Stringy\StaticStringy as Stringy;

use GlideImage;

class XForm
{
    /**
     * Illuminate HtmlBuilder instance.
     *
     * @var \Illuminate\Html\HtmlBuilder
     */
    protected $html;

    /**
     * Illuminate FormBuilder instance.
     *
     * @var \Illuminate\Html\FormBuilder
     */
    protected $form;

    /**
     * Illuminate Repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Illuminate SessionManager instance.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * Prefix for fields names and ids.
     *
     * @var String
     */
    protected $prefix;

    /**
     * @param HtmlBuilder   $html
     * @param FormBuilder   $form
     * @param Config        $config
     * @param Session       $session
     */
    public function __construct(HtmlBuilder $html, FormBuilder $form, Config $config, Session $session)
    {
        $this->html = $html;
        $this->form = $form;
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * Open a form while passing a model and the routes for storing or updating
     * the model. This will set the correct route along with the correct
     * method.
     *
     * @param  array  $options
     * @return string
     */
    public function open(array $options = [])
    {
        // Set the HTML5 role.
        $options['role'] = 'form';

        // If the class hasn't been set, set the default style.
        if ( ! isset($options['class']))
        {
            $defaultForm = $this->getDefaultForm();
            $this->type = 'default';

            if ($defaultForm === 'horizontal')
            {
                $options['class'] = 'form-horizontal';
                $this->type = 'horizontal';
            } elseif ($defaultForm === 'inline')
            {
                $options['class'] = 'form-inline';
                $this->type = 'inline';
            }
        }

        $this->prefix = isset($options['prefix']) ? $options['prefix'] : null;

        if (isset($options['model']))
        {
            return $this->model($options);
        }

        return $this->form->open($options);
    }

    /**
     * IOpen a standard Bootstrap form.
     *
     * @param array $options
     * @return string
     */
    public function openStandard(array $options = [])
    {
        $options = array_merge(['class' => null], $options);
        $this->type = 'default';

        return $this->open($options);
    }

    /**
     * Open an inline Bootstrap form.
     *
     * @param  array  $options
     * @return string
     */
    public function openInline(array $options = [])
    {
        $options = array_merge(['class' => 'form-inline'], $options);
        $this->type = 'inline';

        return $this->open($options);
    }

    /**
     * Open a horizontal Bootstrap form.
     *
     * @param  array  $options
     * @return string
     */
    public function openHorizontal(array $options = [])
    {
        $options = array_merge(['class' => 'form-horizontal'], $options);
        $this->type = 'horizontal';

        return $this->open($options);
    }

    /**
     * Open a form configured for model binding.
     *
     * @param  array  $options
     * @return string
     */
    protected function model($options)
    {
        $model = $options['model'];

        // If the form is passed a model, we'll use the update route to update
        // the model using the PUT method.
        if ($options['model']->exists) {
            $options['route'] = is_array($options['update'])
                                    ? $options['update']
                                    : [$options['update'], $options['model']->getKey()];
            $options['method'] = 'PUT';
        } else {
            // Otherwise, we're storing a brand new model using the POST method.
            $options['route'] = $options['store'];
            $options['method'] = 'POST';
        }

        // Forget the routes provided to the input.
        array_forget($options, ['model', 'update', 'store']);

        return $this->form->model($model, $options);
    }

    /**
     * Close the form
     *
     * @return mixed
     */
    public function close()
    {
        return $this->form->close();
    }

    /**
     * Is the form an horizontal one ?
     * @return boolean
     */
    protected function isHorizontal()
    {
        return $this->type == 'horizontal';
    }

    /**
     * Is the form an inline one ?
     * @return boolean
     */
    protected function isInline()
    {
        return $this->type == 'inline';
    }

    /**
     * Is the form a default one ?
     * @return boolean
     */
    protected function isDefault()
    {
        return $this->type == 'default';
    }

    /**
     * Create a bootstrap static field
     *
     * @param $name
     * @param null $label
     * @param null $value
     * @param array $options
     * @return string
     */
    public function staticField($name, $label = null, $value = null, $options = [])
    {
        $name = $this->prefix.$name;

        $options = array_merge(['class' => 'form-control-static'], $options);

        $label = $this->label(
            $name,
            $this->getLabelTitle($label, $name),
            [],
            isset($options['required']) || in_array('required', $options)
        );

        $inputElement = '<p'.$this->html->attributes($options).'>'.e($value).'</p>';

        $wrapperOptions = ['class' => ($this->isHorizontal()) ? $this->getRightColumnClass() : null];
        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$inputElement.$this->getFieldError($name).'</div>';

        return $this->getFormGroup($name, $label, $groupElement);
    }

    /**
     * Create a Bootstrap text field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function text($name, $label = null, $value = null, $options = [])
    {
        return $this->input('text', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap email field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function email($name = 'email', $label = null, $value = null, $options = [])
    {
        return $this->input('email', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap url field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function url($name = 'url', $label = null, $value = null, $options = [])
    {
        return $this->input('url', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap textarea field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function textarea($name, $label = null, $value = null, $options = [])
    {
        $name = $this->prefix.$name;

        $label = $this->label(
            $name,
            $this->getLabelTitle($label, $name),
            [],
            isset($options['required']) || in_array('required', $options)
        );

        $options = $this->getFieldOptions($options);

        $wrapperOptions = ['class' => ($this->isHorizontal()) ? $this->getRightColumnClass() : null];

        $inputElement = $this->form->textarea($name, $value, $options);

        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$inputElement.$this->getFieldError($name).'</div>';

        $return = $this->getFormGroup($name, $label, $groupElement);

        return $return;
    }

    /**
     * Create a Bootstrap password field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $options
     * @return string
     */
    public function password($name = 'password', $label = null, $options = [])
    {
        return $this->input('password', $name, $label, null, $options);
    }

    /**
     * Create a Bootstrap tel field input
     * @param  string $name
     * @param  string $label
     * @param  string $value
     * @param  array  $options
     * @return string
     */
    public function tel($name, $label = null, $value = null, $options = [])
    {
        return $this->input('tel', $name, $label, $value, $options);
    }


    /**
     * Create a Bootstrap text field input with a submit button
     * @param  string $name
     * @param  string $label
     * @param  string $value
     * @param  array  $options
     * @return string
     */
    public function unicText($name, $label = null, $value = null, $options = [])
    {
        return $this->input('unicText', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap date field input
     * @param  string $name
     * @param  string $label
     * @param  string $value
     * @param  array  $options
     * @return string
     */
    public function date($name, $label = null, $value = null, $options = [])
    {
        return $this->input('date', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap number field input
     * @param  string $name
     * @param  string $label
     * @param  string $value
     * @param  array  $options
     * @return string
     */
    public function number($name, $label = null, $value = null, $options = [])
    {
        return $this->input('number', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap hidden field input
     * @param  string $name
     * @param  string $value
     * @param  array  $options
     * @return string
     */
    public function hidden($name, $value = null, $options = [])
    {
        return $this->form->input('hidden', $name, $value, $options);
    }

    /**
     * Create a Bootstrap checkbox input.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  string   $value
     * @param  boolean  $checked
     * @param  boolean  $inline
     * @param  array    $options
     * @return string
     */
    public function checkbox($name, $label, $value, $checked = null, $inline = false, $options = [])
    {
        $name = $this->prefix.$name;

        $labelOptions = $inline ? ['class' => 'checkbox-inline'] : [];

        $inputElement = $this->form->checkbox($name, $value, $checked, $options);
        $labelElement = '<label '.$this->html->attributes($labelOptions).'>'.$inputElement.$label.'</label>';

        return $inline ? $labelElement : '<div class="checkbox">'.$labelElement.'</div>';
    }

    /**
     * Create a collection of Bootstrap checkboxes.
     *
     * @param  string $name
     * @param  string $label
     * @param  array $choices
     * @param  array $checkedValues
     * @param  boolean $inline
     * @param  array $options
     * @return string
     */
    public function checkboxes($name, $label = null, $choices = [], $checkedValues = [], $inline = false, $options = [])
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel)
        {
            $checked = in_array($value, (array) $checkedValues);

            $elements .= $this->checkbox($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        return $this->getFormGroup($name, $label, $elements);
    }

    /**
     * Create a Bootstrap radio input.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  string   $value
     * @param  boolean  $checked
     * @param  boolean  $inline
     * @param  array    $options
     * @return string
     */
    public function radio($name, $label, $value, $checked = null, $inline = false, $options = [])
    {
        $name = $this->prefix.$name;

        $labelOptions = $inline ? ['class' => 'radio-inline'] : [];

        $inputElement = $this->form->radio($name, $value, $checked, $options);
        $labelElement = '<label '.$this->html->attributes($labelOptions).'>'.$inputElement.$label.'</label>';

        return $inline ? $labelElement : '<div class="radio">'.$labelElement.'</div>';
    }

    /**
     * Create a collection of Bootstrap radio inputs.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  array    $choices
     * @param  string   $checkedValue
     * @param  boolean  $inline
     * @param  array    $options
     * @return string
     */
    public function radios($name, $label = null, $choices = [], $checkedValue = null, $inline = false, $options = [])
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel)
        {
            $checked = $value === $checkedValue;

            $elements .= $this->radio($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        return $this->getFormGroup($name, $label, $elements);
    }

    /**
     * Create a Boostrap submit button.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function submit($value = null, $options = [])
    {
        $options = array_merge(['class' => 'btn btn-primary'], $options);

        return $this->form->submit($value, $options);
    }

    /**
     * Create a Bootstrap button.
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function button($value = null, $options = [])
    {
        return $this->form->button($value, $options);
    }

    /**
     * Return a img file input, with display box and delete link if exist
     * @param  string $name
     * @param  string $label
     * @param  string $value
     * @param  array  $options
     * @return string
     */
    public function img($name, $label = null, $value = null, $options = [])
    {
        $name = $this->prefix.$name;

        $label = $this->label(
            $name,
            $this->getLabelTitle($label, $name),
            [],
            isset($options['required']) || in_array('required', $options)
        );

        $route = '';
        if (!empty($options['route']))
        {
            $route = $options['route'];
            unset($options['route']);
        }

        $inputOptions = $this->getFieldOptions($options);
        $inputElement = $this->form->input('file', $name, $value, $inputOptions);

        $wrapperOptions['class'] = '';

        if ($this->isHorizontal()) {
            $wrapperOptions['class'] .= ' '.$this->getRightColumnClass();
        }

        $groupElement =
            '<div '.$this->html->attributes($wrapperOptions).'>'.
                $inputElement.
                $this->getFieldError($name).
            '</div>';

        if (!empty($value))
        {
            $groupElement =
                '<div class="img-container">'.
                    '<a href="'.$route.'/delete-document/'.$name.'" '.
                    'data-confirm="Confirmez-vous la suppression de cette image ?"><i></i></a>'.
                    '<img src="'. GlideImage::load($value) .'">'.
                '</div>'.
                $groupElement;
        }

        $return = $this->getFormGroup($name, $label, $groupElement);

        return $return;
    }

    /**
     * Return a pdf file input, with display and delete links if exist
     * @param  string $name
     * @param  string $label
     * @param  string $value
     * @param  array  $options
     * @return string
     */
    public function pdf($name, $label = null, $value = null, $options = [])
    {
        $name = $this->prefix.$name;

        $label = $this->label(
            $name,
            $this->getLabelTitle($label, $name),
            [],
            isset($options['required']) || in_array('required', $options)
        );

        $route = '';

        if (!empty($options['route']))
        {
            $route = $options['route'];
            unset($options['route']);
        }

        $options = $this->getFieldOptions($options);
        $wrapperOptions = ['class' => ($this->isHorizontal()) ? $this->getRightColumnClass() : null];
        $inputElement = $this->form->input('file', $name, $value, $options);

        $return = '<div class="row">';
        $return .= '<div class="col-md-6">'.$inputElement.$this->getFieldError($name).'</div>';
        if (!empty($value))
        {
            $return .= '<div class="col-md-3">';
            $return .= '<a href="'.$value.'" target="_blank" class="btn-pdf"><i></i> <span>Afficher</span></a>';
            $return .= '</div>';
            $return .= '<div class="col-md-3">';
            $return .= '<a href="'.$route.'/delete-document/'.$name.'" class="btn-delete-file"  data-confirm="Confirmez-vous la suppression de ce document ?"><i></i> <span>Supprimer</span></a>';
            $return .= '</div>';
        }
        else
        {
            $return .= '<div class="col-md-6"></div>';
        }
        $return .= '</div>';

        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$return.'</div>';

        return $this->getFormGroup($name, $label, $groupElement);
    }

   /**
     * Create the input group for an element with the correct classes for errors.
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function input($type, $name, $label = null, $value = null, $options = [])
    {
        $name = $this->prefix.$name;

        $label = $this->label(
            $name,
            $this->getLabelTitle($label, $name),
            [],
            isset($options['required']) || in_array('required', $options)
        );

        $options = $this->getFieldOptions($options);
        $wrapperOptions = ['class' => ($this->isHorizontal()) ? $this->getRightColumnClass() : null];

        if ($type == 'password') {
            $inputElement = $this->form->password($name, $options);
        }
        else if ($type == 'unicText') {
            $inputElement = $this->form->input('text', $name, $value, $options);
        }
        else if ($type == 'date') {
            $inputElement = $this->form->input('text', $name, $value, $options);
        } else {
            $inputElement = $this->form->input($type, $name, $value, $options);
        }

        $currentValue = $this->form->getCurrentValue();
        if ($type == 'unicText')
        {
            $inputGroupClass = preg_match("#input-sm#", $options['class'])
                                    ? 'input-group input-group-sm'
                                    :'input-group';
            $inputElement =
                '<div class="'.$inputGroupClass.'">' .
                    $inputElement .
                    '<span class="input-group-btn">' .
                        $this->button('<i class="fa fa-plus"></i> <span>'.trans('buttons.add').'</span>', ['type' => 'submit', 'class' => 'btn btn-success']).
                    '</span>' .
                '</div>';
        }
        elseif ($type == 'email')
        {
            $inputGroupClass = preg_match("#input-sm#", $options['class'])
                                    ? 'input-group input-group-sm'
                                    :'input-group';
            $inputElement =
                '<div class="'.$inputGroupClass.'">' .
                    $inputElement .
                    '<span class="input-group-btn">' .
                        '<a href="mailto:' . $currentValue .
                        '" class="btn btn-default' .
                        (empty($currentValue) ? ' disabled': '') .
                        '"><i class="fa fa-envelope"></i></a>' .
                    '</span>' .
                '</div>';
        }
        elseif ($type == 'tel')
        {
            $inputGroupClass = preg_match("#input-sm#", $options['class'])
                                    ? 'input-group input-group-sm'
                                    :'input-group';
            $inputElement =
                '<div class="'.$inputGroupClass.'">' .
                    $inputElement .
                    '<span class="input-group-btn">' .
                        '<a href="tel:' . $currentValue .
                        '" class="btn btn-default' .
                        (empty($currentValue) ? ' disabled': '') .
                        '"><i class="fa fa-phone"></i></a>' .
                    '</span>' .
                '</div>';
        }
        elseif ($type == 'url')
        {
            $inputGroupClass = preg_match("#input-sm#", $options['class'])
                                    ? 'input-group input-group-sm'
                                    :'input-group';
            $inputElement =
                '<div class="'.$inputGroupClass.'">' .
                    $inputElement .
                    '<span class="input-group-btn">' .
                        '<a href="' . $currentValue .
                        '" class="btn btn-default' .
                        (empty($currentValue) ? ' disabled': '') .
                        '"><i class="fa fa-link"></i></a>' .
                    '</span>' .
                '</div>';
        }
        elseif ($type == 'date')
        {
            $inputGroupClass = preg_match("#input-sm#", $options['class'])
                                    ? 'input-group input-group-sm'
                                    : 'input-group';
            $inputElement =
                '<div class="'.$inputGroupClass.' input-append date">' .
                    $inputElement .
                    '<span class="input-group-btn"><button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button></span>' .
                '</div>';
        }

        if ($this->isInline()) {
            $groupElement = ' ' . $inputElement . $this->getFieldError($name);
        }
        else
        {
            $groupElement =
                '<div '.$this->html->attributes($wrapperOptions).'>'.
                $inputElement.$this->getFieldError($name).
                '</div>';
        }

        return $this->getFormGroup($name, $label, $groupElement);
    }

    /**
     * Create a bootstrap select
     *
     * @param  string $name
     * @param  array $list
     * @param  string $label
     * @param  mixed $value
     * @param  array  $options
     * @return string
     */
    public function select($name, $list, $label = null, $value = null, $options = [])
    {
        $name = $this->prefix.$name;

        $label = $this->label(
            $name,
            $this->getLabelTitle($label, $name),
            [],
            isset($options['required']) || in_array('required', $options)
        );

        $options = $this->getFieldOptions($options);
        $wrapperOptions = ['class' => ($this->isHorizontal()) ? $this->getRightColumnClass() : null];

        $inputElement = $this->form->select($name, $list, $value, $options);

        if ($this->isInline()) {
            $groupElement = ' ' . $inputElement . $this->getFieldError($name);
        }
        else
        {
            $groupElement =
                '<div '.$this->html->attributes($wrapperOptions).'>'.
                $inputElement.$this->getFieldError($name).
                '</div>';
        }

        return $this->getFormGroup($name, $label, $groupElement);
    }

    /**
     * Create a Bootstrap label.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function label($name, $value = null, $options = [], $required = false)
    {
        if ($value === false) {
            return '';
        }
        $options = $this->getLabelOptions($options);
        return ($required)
                    ? sprintf($this->form->label($name, $value.' %s', $options), $this->getHtmlRequired())
                    : $this->form->label($name, $value, $options);
    }

    /**
     * Get the label title for a form field, first by using the provided one
     * or titleizing the field name.
     *
     * @param  string  $label
     * @param  string  $name
     * @return string
     */
    protected function getLabelTitle($label, $name)
    {
        if ($label === false) {
            return false;
        }
        return $label ?: Str::title($name);
    }

    /**
     * Merge the options provided for a form group with the default options
     * required for Bootstrap styling.
     *
     * @param  string $name
     * @param  array  $options
     * @return array
     */
    protected function getFormGroupOptions($name, $options = [])
    {
        $class = trim('form-group ' . $this->getFieldErrorClass($name));

        return array_merge(['class' => $class], $options);
    }

    /**
     * Merge the options provided for a field with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getFieldOptions($options = [])
    {
        $options['class'] = trim('form-control ' . $this->getFieldOptionsClass($options));

        return $options;
    }

    /**
     * Returns the class property from the options, or the empty string
     *
     * @param   $options
     * @return  string
     */
    protected function getFieldOptionsClass($options)
    {
        return array_get($options, 'class');
    }

    /**
     * Merge the options provided for a label with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getLabelOptions($options = [])
    {
        $class = trim('control-label ' . (($this->isHorizontal()) ? $this->getLeftColumnClass() : ''));

        return array_merge(['class' => $class], $options);
    }

    /**
     * Get the default form style.
     *
     * @return string
     */
    protected function getDefaultForm()
    {
        return $this->config->get('xform.default_form');
    }

    /**
     * Get the column class for the left class of a horizontal form.
     *
     * @return string
     */
    protected function getLeftColumnClass()
    {
        return $this->config->get('xform.left_column');
    }

    /**
     * Get the column class for the right class of a horizontal form.
     *
     * @return string
     */
    protected function getRightColumnClass()
    {
        return $this->config->get('xform.right_column');
    }

    /**
     * Get the MessageBag of errors that is populated by the
     * validator.
     *
     * @return \Illuminate\Support\MessageBag
     */
    protected function getErrors()
    {
        return $this->session->get('errors');
    }

    /**
     * Get the first error for a given field, using the provided
     * format, defaulting to the normal Bootstrap 3 format.
     *
     * @param  string  $field
     * @param  string  $format
     * @return mixed
     */
    protected function getFieldError($field, $format = '<span class="help-block">:message</span>')
    {
        $field = Stringy::removeLeft($field, $this->prefix);
        if ($this->getErrors())
        {
            $allErrors = $this->config->get('xform.all_errors');

            if ($allErrors)
            {
                return $this->getErrors()->get($field, $format);
            }

            return $this->getErrors()->first($field, $format);
        }
    }

    /**
     * Return the error class if the given field has associated
     * errors, defaulting to the normal Bootstrap 3 error class.
     *
     * @param  string  $field
     * @param  string  $class
     * @return string
     */
    protected function getFieldErrorClass($field, $class = 'has-error')
    {
        return $this->getFieldError($field) ? $class : null;
    }

    /**
     * Get a form group comprised of a label, form element and errors.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $element
     * @return string
     */
    protected function getFormGroup($name, $label, $element)
    {
        $options = $this->getFormGroupOptions($name);

        return '<div '.$this->html->attributes($options).'>'.$label.$element.'</div>';
    }

    /**
     * Return the html to append to required field's labels
     * @return string
     */
    protected function getHtmlRequired()
    {
        return '<sup><span class="required"></span></sup><span class="sr-only">Requis</span>';
    }

}
