<?php

declare(strict_types=1);

namespace Tests\Controller;

use Src\Model\User;
use PHPUnit\Framework\TestCase;
use Src\Controller\AuthController;
use Src\Repository\UserRepository;

class AuthControllerTest extends TestCase
{
    protected AuthController $controller;
    protected $userRepositoryMock;
    protected $userMock;

    protected function setUp(): void
    {
        $_ENV['SECRET_KEY'] = 'key_test';
        $this->userMock = $this->createMock(User::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->controller = new AuthController($this->userRepositoryMock);
    }

    public static function invalidIdProvider(): array
    {
        return [
            ['string'],
            ['1.1'],
            [1.1],
            [null],
            ['']
        ];
    }

    public function testLoginInvalidCretentials(): void
    {
        $_POST['email']     = 'lucas@mail.com';
        $_POST['password']  = 'password123';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findUserByEmail')
            ->willReturn($this->userMock);

        $this->userMock
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn('password123');

        $response = $this->controller->login();

        $this->assertEquals('FORBIDDEN', $response['status']);
    }

    public function testLoginValidCretentials(): void
    {
        $_POST['email']     = 'lucas@mail.com';
        $_POST['password']  = 'password123';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findUserByEmail')
            ->willReturn($this->userMock);

        $this->userMock
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn(password_hash('password123', PASSWORD_BCRYPT));

        $response = $this->controller->login();

        $this->assertEquals('SUCCESS', $response['status']);
    }
}
