<?php

namespace App\Enums;

enum FormFiledType: string
{
    case TextField = 'text';
    case NumberField = 'number';
    case DateField = 'date';
    case Dropdownfield = 'dropdown';
    case RadioButton = 'radio';
    case CheckboxButton = 'checkbox';
    case UploadButton = 'upload';
}
