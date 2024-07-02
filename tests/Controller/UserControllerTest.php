<?php

declare(strict_types=1);

namespace Tests\Controller;

use Src\Model\User;
use PHPUnit\Framework\TestCase;
use Src\Helper\HttpRequestHelper;
use Src\Controller\UserController;
use Src\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;

class UserControllerTest extends TestCase
{
    protected UserController $controller;
    protected $userMock;
    protected $userRepositoryMock;
    protected $httpRequestHelperMock;

    protected function setUp(): void
    {
        $this->userMock = $this->createMock(User::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->httpRequestHelperMock = $this->createMock(HttpRequestHelper::class);
        $this->controller = new UserController($this->userRepositoryMock, $this->httpRequestHelperMock);
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

    public function testRegisterWithValidData(): void
    {
        $_POST['name']      = 'Lucas Test';
        $_POST['email']     = 'lucas@mail.com';
        $_POST['password']  = 'password123';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $response = $this->controller->register();

        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function testRegisterWithInvalidEmail(): void
    {
        $_POST = [
            'email'     => 'test',
            'name'      => 'test',
            'password'  => '123456'
        ];

        $response = $this->controller->register();

        $this->assertEquals('Invalid email', $response['message']);
    }

    public function testRegisterWithMissingParameters(): void
    {
        $_POST = ['email' => 'test@mail.com'];
        $response = $this->controller->register();

        $this->assertSame('MISSING_PARAMETERS', $response['status']);
        $this->assertStringContainsString('Missing parameters', $response['message']);
    }

    /**
     * @dataProvider invalidIdProvider
     */    public function testGetDataWithInvalidId($invalidId): void
    {
        $params = ['id' => $invalidId];

        $response = $this->controller->getData($params);

        $this->assertEquals('INVALID_PARAMETER', $response['status']);
    }

    public function testGetDataWithValidId(): void
    {
        $params = ['id' => 1];

        $response = $this->controller->getData($params);

        $this->assertArrayHasKey('user', $response);
        $this->assertArrayHasKey('addresses', $response);
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function testUpdateWithInvalidData($invalidId): void
    {
        $params = [
            'id'    => $invalidId,
            'name'  => 'Lucas Test',
            'email' => 'lucas@mail.com'
        ];

        $response = $this->controller->update($params);
        $this->assertEquals('INVALID_PARAMETER', $response['status']);
    }

    public function testUpdateWithValidData(): void
    {
        $params = [
            'id'    => 1,
            'name'  => 'Lucas Test',
            'email' => 'lucas@mail.com'
        ];

        $jsonData = [
            'name'  => 'Lucas Test',
            'email' => 'lucas@mail.com'
        ];

        $this->httpRequestHelperMock->expects($this->once())
            ->method('getInputStreamParams')
            ->with('PUT')
            ->willReturn($jsonData);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('getModel')
            ->with($params['id'], true)
            ->willReturn($this->userMock);

        $this->userMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($params['id']);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->userMock)
            ->willReturn(true);

        $response = $this->controller->update($params);

        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function testUpdateUserNotFound(): void
    {
        $params = [
            'id'    => 1,
            'name'  => 'Lucas Test',
            'email' => 'lucas@mail.com'
        ];

        $jsonData = [
            'name'  => 'Lucas Test',
            'email' => 'lucas@mail.com'
        ];

        $this->httpRequestHelperMock
            ->expects($this->once())
            ->method('getInputStreamParams')
            ->with('PUT')
            ->willReturn($jsonData);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('getModel')
            ->with($params['id'], true)
            ->willReturn($this->userMock);

        $response = $this->controller->update($params);

        $this->assertEquals('NOT_FOUND', $response['status']);
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function testDeleteWithInvalidData($invalidId): void
    {
        $params = [
            'id' => $invalidId,
        ];

        $response = $this->controller->delete($params);
        $this->assertEquals('INVALID_PARAMETER', $response['status']);
    }

    public function testDeleteWithValidData(): void
    {
        $params = [
            'id' => 1,
        ];

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with($params['id'])
            ->willReturn(true);

        $response = $this->controller->delete($params);
        $this->assertEquals('SUCCESS', $response['status']);
    }
}
