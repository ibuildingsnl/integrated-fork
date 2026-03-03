<?php

namespace Integrated\Bundle\UserBundle\Tests\DependencyInjection\Compiler;

use Integrated\Bundle\UserBundle\DependencyInjection\Compiler\RegisterRolesParametersPass;
use PHPUnit\Framework\TestCase;

class RegisterRolesParametersPassTest extends TestCase
{
    public function testAddParametersOnlyRegistersRolePrefixedEntries(): void
    {
        $baseDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'integrated_roles_'.uniqid('', true);
        $rolesDir = $baseDir.'/Resources/config/roles';
        mkdir($rolesDir, 0777, true);

        file_put_contents($rolesDir.'/roles.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<roles>
    <role><name>MANAGER</name><label>Manager</label></role>
    <role><name>ROLE_USER_MANAGER</name><label>User manager</label></role>
</roles>
XML
        );

        $parameters = [];
        $pass = new RegisterRolesParametersPass();
        $method = new \ReflectionMethod(RegisterRolesParametersPass::class, 'addParameters');
        $method->setAccessible(true);
        $arguments = [$baseDir, &$parameters];
        $method->invokeArgs($pass, $arguments);

        self::assertArrayHasKey('ROLE_USER_MANAGER', $parameters);
        self::assertArrayNotHasKey('MANAGER', $parameters);

        @unlink($rolesDir.'/roles.xml');
        @rmdir($rolesDir);
        @rmdir($baseDir.'/Resources/config');
        @rmdir($baseDir.'/Resources');
        @rmdir($baseDir);
    }
}
