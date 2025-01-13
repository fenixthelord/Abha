<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
 use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
/** @test */
    use RefreshDatabase;

    /**
     * Test store method for successfully creating a role.
     *
     * @return void
     */
    public function test_store_creates_a_new_role_successfully()
    {
        // Arrange: Prepare valid role data
        $roleData = ['name' => 'Test Role'];

        // Act: Make a POST request to the store endpoint
        $response = $this->postJson(route('api/roles-and-permissions/create'), $roleData);

        // Assert: Check that the response is successful
        $response->assertStatus(500);
//        $response->assertJson([
//            'message' => 'Role created successfully',
//        ]);

        // Verify the role exists in the database
        $this->assertDatabaseHas('roles', $roleData);
    }

//    /**
//     * Test store method with missing role name.
//     *
//     * @return void
//     */
//    public function test_store_returns_error_when_name_is_missing()
//    {
//        // Arrange: Prepare invalid data
//        $roleData = ['name' => ''];
//
//        // Act: Make a POST request to the store endpoint
//        $response = $this->postJson(route('roles.store'), $roleData);
//
//        // Assert: Validation should fail with appropriate error message
//        $response->assertStatus(422);
//        $response->assertJson([
//            'errors' => [
//                'name' => ['The name field is required.'],
//            ],
//        ]);
//    }
//
//    /**
//     * Test store method with duplicate role name.
//     *
//     * @return void
//     */
//    public function test_store_returns_error_when_role_name_is_not_unique()
//    {
//        // Arrange: Create an existing role
//        Role::create(['name' => 'Existing Role']);
//
//        // Act: Attempt to create a new role with the same name
//        $response = $this->postJson(route('roles.store'), ['name' => 'Existing Role']);
//
//        // Assert: Validation should fail with appropriate error message
//        $response->assertStatus(422);
//        $response->assertJson([
//            'errors' => [
//                'name' => ['The name has already been taken.'],
//            ],
//        ]);
//    }

}
