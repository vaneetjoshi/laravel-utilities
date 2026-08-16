<?php

namespace Vaneetjoshi\LaravelUtilities\Widgets\Enums;

/**
 * Standard Widget Colors
 * 
 * Maps to standard semantic UI colors. Developers can pass these enum values,
 * or fall back to custom strings (e.g., 'emerald', '#ff5733') if they need
 * to extend colors via configurations.
 */
enum WidgetColor: string
{
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case SUCCESS = 'success';
    case DANGER = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
    case NEUTRAL = 'neutral';
}