<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Enums;

enum InputType: string
{
    case TEXT = 'text';
    case EMAIL = 'email';
    case NUMBER = 'number';
    case PASSWORD = 'password';
    case TEXTAREA = 'textarea';
    
    case CHECKBOX = 'checkbox';
    case SELECT = 'select';
    case MULTI_SELECT = 'multi_select';
    
    case IMAGE = 'image';
    case FILE = 'file';
    
    case DATE = 'date';
    case DATETIME = 'datetime';
    
    case ARRAY = 'array'; 
    case KEY_VALUE = 'key_value'; 
}