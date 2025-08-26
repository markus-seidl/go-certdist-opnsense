<?php

namespace OPNsense\GoCertdist\FieldTypes;

use OPNsense\Base\FieldTypes\TextField;
use OPNsense\Base\Validators\CallbackValidator;

/**
 * Class YamlField
 * @package OPNsense\Base\FieldTypes
 */
class YamlField extends TextField
{
    /**
     * @var bool marks if this is a data attribute or a container
     */
    protected $internalIsContainer = false;

    /**
     * @var string default validation message string
     */
    protected $internalValidationMessage = "Please enter valid YAML configuration";

    /**
     * retrieve field validators for this field type
     * @return array returns Text/regex validator
     */
    public function getValidators()
    {
        $validators = parent::getValidators();
        
        // Add YAML validation
        $validators[] = new CallbackValidator(array(
            "callback" => array($this, "validateYaml"),
            "message" => $this->getValidationMessage()
        ));
        
        return $validators;
    }

    /**
     * Validate YAML syntax
     * @param string $value
     * @return bool
     */
    public function validateYaml($value)
    {
        // Empty values are allowed if not required
        if (empty(trim($value))) {
            return true;
        }
        
        // Try PHP's built-in yaml_parse if available
        if (function_exists('yaml_parse')) {
            $result = @yaml_parse($value);
            return $result !== false;
        }

        return false;
    }
}
