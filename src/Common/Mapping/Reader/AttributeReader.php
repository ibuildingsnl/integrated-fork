<?php

namespace Integrated\Common\Mapping\Reader;

final class AttributeReader
{
    public function getClassAttributes(\ReflectionClass $class): array
    {
        return $this->convertToAttributeInstances($class->getAttributes());
    }

    public function getClassAttribute(\ReflectionClass $class, string $attributeName): ?object
    {
        foreach ($this->getClassAttributes($class) as $attribute) {
            if ($attribute instanceof $attributeName) {
                return $attribute;
            }
        }

        return null;
    }

    public function getPropertyAttributes(\ReflectionProperty $property): array
    {
        return $this->convertToAttributeInstances($property->getAttributes());
    }

    public function getPropertyAttribute(\ReflectionProperty $property, $attributeName): ?object
    {
        foreach ($this->getPropertyAttributes($property) as $attribute) {
            if ($attribute instanceof $attributeName) {
                return $attribute;
            }
        }

        return null;
    }

    private function convertToAttributeInstances(array $attributes): array
    {
        $instances = [];

        foreach ($attributes as $attribute) {
            $instances[] = $attribute->newInstance();
        }

        return $instances;
    }
}
