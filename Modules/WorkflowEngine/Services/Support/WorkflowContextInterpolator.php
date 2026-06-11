<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services\Support;

final class WorkflowContextInterpolator
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function interpolate(string $template, array $context): string
    {
        $result = preg_replace_callback(
            '/\{\{([^}]+)\}\}/',
            static function (array $matches) use ($context): string {
                $value = data_get($context, trim($matches[1]));

                if (is_array($value) || is_object($value)) {
                    return json_encode($value, JSON_THROW_ON_ERROR);
                }

                if ($value === null) {
                    return '';
                }

                if (is_bool($value)) {
                    return $value ? 'true' : 'false';
                }

                return (string) $value;
            },
            $template,
        );

        return $result ?? $template;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function resolvePath(string $path, array $context): mixed
    {
        return data_get($context, $path);
    }
}
