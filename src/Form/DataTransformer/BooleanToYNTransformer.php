<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-01-07
 * Time: 16:26
 */

namespace App\Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BooleanToYNTransformer implements DataTransformerInterface
{
    /**
     * @param string|null "N"
     * @return boolean
     * @throws TransformationFailedException
     */
    public function Transform($value): bool
    {
        if ($value == 'Y') {
            return true;
        } elseif ($value == 'N') {
            return false;
        } else {
            throw new TransformationFailedException(sprintf('Unable to transform "%s" to boolean.  Valid values are either Y or N.', $value));
        }
    }

    /**
     * @param boolean|null false
     * @return string
     */
    public function reverseTransform($value): string
    {
        return $value === true ? 'Y' : 'N';
    }
}