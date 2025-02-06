<?php

namespace App\Enums;

enum FormFiledType: string
{
    case TextField = 'text';
    case NumberField = 'number';
    case DateField = 'date';
    case DropdownField = 'dropdown';
    case RadioButton = 'radio';
    case CheckboxButton = 'checkbox';
    case FileButton = 'file';
    case MapField = 'map';
}
