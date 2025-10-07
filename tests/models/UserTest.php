<?php
use PHPUnit\Framework\TestCase;

// Incluimos los archivos necesarios para la prueba
require_once __DIR__ . '/../../models/User.php';

class UserTest extends TestCase
{
    /**
     * @test
     * Prueba que la clase User se puede instanciar correctamente.
     */
    public function testUserCanBeInstantiated()
    {
        // Para probar la clase User de forma aislada (unit test),
        // creamos un "mock" del objeto de base de datos (PDO).
        // Así no dependemos de una conexión real.
        $mockDbConnection = $this->createMock(PDO::class);

        // Instanciamos la clase User, pasándole nuestro objeto simulado.
        $user = new User($mockDbConnection);

        // Usamos una aserción de PHPUnit para verificar que el objeto creado
        // es una instancia de la clase User. Si esto es cierto, el test pasa.
        $this->assertInstanceOf(User::class, $user);
    }
}
