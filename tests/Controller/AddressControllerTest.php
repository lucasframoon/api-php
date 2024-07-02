<?php

declare(strict_types=1);

namespace Tests\Controller;

use Src\Model\Address;
use PHPUnit\Framework\TestCase;
use Src\Helper\HttpRequestHelper;
use Src\Controller\AddressController;
use Src\Repository\AddressRepository;
use PHPUnit\Framework\Attributes\DataProvider;

class AddressControllerTest extends TestCase
{
    protected AddressController $controller;
    protected $addressMock;
    protected $addressRepositoryMock;
    protected $httpRequestHelperMock;

    protected function setUp(): void
    {
        $this->addressMock = $this->createMock(Address::class);
        $this->addressRepositoryMock = $this->createMock(AddressRepository::class);
        $this->httpRequestHelperMock = $this->createMock(HttpRequestHelper::class);
        $this->controller = new AddressController($this->addressMock, $this->addressRepositoryMock, $this->httpRequestHelperMock);
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

    public static function invalidParametersProvider(): array
    {
        return [
            [
                [
                    'user_id'   => 1,
                    'params'    => [
                        'state'         => 'state',
                        'postal_code'   => '123456',
                        'country'       => 'country'
                    ]
                ]
            ],
            [
                [
                    'user_id'   => 1,
                    'params'    => [
                        'street'        => 'street',
                        'city'          => 'city',
                        'postal_code'   => '123456',
                        'country'       => 'country'
                    ]
                ]
            ],
            [
                [
                    'user_id'   => 1,
                    'params'    => [
                        'street'        => 'street',
                        'city'          => 'city',
                        'state'         => 'state',
                        'country'       => 'country'
                    ]
                ]
            ],
            [
                [
                    'user_id'   => 1,
                    'params'    => [
                        'street'        => 'street',
                        'city'          => 'city',
                        'state'         => 'state',
                        'postal_code'   => '123456',
                    ]
                ]
            ],
            [
                [
                    'user_id'   => null,
                    'params'    => [
                        'street'        => 'street',
                        'city'          => 'city',
                        'state'         => 'state',
                        'postal_code'   => '123456',
                        'country'       => 'country'
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider invalidParametersProvider
     *
     */
    public function testNewWithInvalidData($invalidData): void
    {
        $_POST['street']        = $invalidData['params']['street'] ?? null;
        $_POST['city']          = $invalidData['params']['city'] ?? null;
        $_POST['state']         = $invalidData['params']['state'] ?? null;
        $_POST['postal_code']   = $invalidData['params']['123456'] ?? null;
        $_POST['country']       = $invalidData['params']['country'] ?? null;
        $_SESSION['user_id']    =  $invalidData['user_id'];

        $response = $this->controller->new();

        $this->assertTrue(in_array($response['status'], ['MISSING_PARAMETERS', 'UNAUTHORIZED']));
    }

    public function testNewWithValidData(): void
    {
        $_POST['street']        = 'street';
        $_POST['city']          = 'city';
        $_POST['state']         = 'state';
        $_POST['postal_code']   = '123456';
        $_POST['country']       = 'country';
        $_SESSION['user_id']    = 1;

        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $response = $this->controller->new();

        $this->assertEquals('SUCCESS', $response['status']);
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function testGetDataWithInvalidId($invalidId): void
    {
        $params = ['id' => $invalidId];

        $response = $this->controller->getData($params);

        $this->assertEquals('INVALID_PARAMETER', $response['status']);
    }

    public function testGetDataWithValidId(): void
    {
        $params = ['id' => 1];

        $response = $this->controller->getData($params);

        $this->assertArrayHasKey('address', $response);
    }

    public function testGetUserAddressesWithValidId(): void
    {
        $params = ['id' => 1];
        $_SESSION['user_id'] = 1;

        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('findByUserId')
            ->with($_SESSION['user_id'], [])
            ->willReturn([
                "id"            => "1",
                "user_id"       => "13",
                "street"        => "street",
                "city"          => "city",
                "state"         => "state",
                "postal_code"   => "54762290",
                "country"       => "Brasil",
                "created_at"    => "2024-06-30 03:14:59",
                "updated_at"    => "2024-06-30 17:32:35"
            ]);

        $response = $this->controller->getUserAddresses($params);

        $this->assertArrayHasKey('addresses', $response);
        $this->assertGreaterThan(0, count($response['addresses']));
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function testUpdateWithInvalidData($invalidId): void
    {
        $params = ['id' => $invalidId];

        $response = $this->controller->update($params);
        $this->assertEquals('INVALID_PARAMETER', $response['status']);
    }

    public function testUpdateWithValidData(): void
    {
        $params = ['id' => 1];
        $_SESSION['user_id'] = 10;
        $jsonData = [
            "street"      => "street",
            "city"        => "city",
            "state"       => "state",
            "postal_code" => "54762290",
            "country"     => "Brasil"
        ];
        $expectedUpdateData = [
            'user_id'     => 10,
            'street'      => "street",
            'city'        => "city",
            'state'       => "state",
            'postal_code' => "54762290",
            'country'     => "Brasil"
        ];

        $this->httpRequestHelperMock->expects($this->once())
            ->method('getInputStreamParams')
            ->with('PUT')
            ->willReturn($jsonData);

        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('getModel')
            ->with($params['id'])
            ->willReturn($this->addressMock);

        $this->addressMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($params['id']);


        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->addressMock)
            ->willReturn(true);

        $response = $this->controller->update($params);

        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function testUpdateUserNotFound(): void
    {
        $params = ['id' => 1];
        $_SESSION['user_id'] = 10;
        $jsonData = [
            "street"      => "street",
            "city"        => "city",
            "state"       => "state",
            "postal_code" => "54762290",
            "country"     => "Brasil"
        ];
        $expectedUpdateData = [
            'user_id'     => 10,
            'street'      => "street",
            'city'        => "city",
            'state'       => "state",
            'postal_code' => "54762290",
            'country'     => "Brasil"
        ];

        $this->httpRequestHelperMock
            ->expects($this->once())
            ->method('getInputStreamParams')
            ->with('PUT')
            ->willReturn($jsonData);

        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('getModel')
            ->with($params['id'])
            ->willReturn($this->addressMock);

        $response = $this->controller->update($params);

        $this->assertEquals('NOT_FOUND', $response['status']);
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function testDeleteWithInvalidData($invalidId): void
    {
        $params = ['id' => $invalidId];

        $response = $this->controller->delete($params);
        $this->assertEquals('INVALID_PARAMETER', $response['status']);
    }

    public function testDeleteWithValidData(): void
    {
        $params = ['id' => 1];

        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with($params['id'])
            ->willReturn(true);

        $response = $this->controller->delete($params);
        $this->assertEquals('SUCCESS', $response['status']);
    }
}
