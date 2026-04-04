<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckLicense;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Middleware\RoleMiddleware;
use Tests\TestCase;

class ExpenseNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            CheckLicense::class,
            RoleMiddleware::class,
        ]);
    }

    public function test_front_desk_expense_creation_notifies_finance_roles_but_not_front_desk_bell(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-expense@example.test');
        $adminUser = $this->createUserWithRole('admin', 'admin-expense@example.test');
        $managerUser = $this->createUserWithRole('manager', 'manager-expense@example.test');
        $accountantUser = $this->createUserWithRole('accountant', 'accountant-expense@example.test');

        $category = ExpenseCategory::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'description' => 'Operational expenses',
            'is_active' => true,
        ]);

        $this->actingAs($frontDeskUser)
            ->post(route('front-desk.expenses.store'), [
                'expense_category_id' => $category->id,
                'vendor_name' => 'Office Supplier',
                'description' => 'Printer paper restock',
                'expense_date' => now()->toDateString(),
                'amount' => '3000',
                'payment_method' => 'cash',
                'receipt_number' => 'RCPT-1001',
                'notes' => 'Front desk operational expense',
            ])
            ->assertRedirect(route('front-desk.expenses.index'));

        $expense = Expense::query()->latest('id')->first();

        $this->assertNotNull($expense);
        $this->assertSame('3000.00', $expense->amount);

        $this->actingAs($frontDeskUser)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $expectedBody = sprintf('Expense %s for 3,000.00 was submitted by %s.', $expense->expense_number, $frontDeskUser->full_name);

        $this->actingAs($adminUser)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.title', 'New expense submitted')
            ->assertJsonPath('items.0.body', $expectedBody)
            ->assertJsonPath('items.0.action_url', route('admin.expenses.show', $expense->id));

        $this->actingAs($managerUser)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.action_url', route('manager.expenses.show', $expense->id));

        $this->actingAs($accountantUser)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.action_url', route('accountant.expenses.show', $expense->id));
    }

    public function test_front_desk_expense_pages_use_front_desk_route_prefix(): void
    {
        $frontDeskUser = $this->createUserWithRole('front_desk', 'frontdesk-route-prefix@example.test');

        $this->actingAs($frontDeskUser)
            ->get(route('front-desk.expenses.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Expenses/Index')
                ->where('routePrefix', 'front-desk'));

        $this->actingAs($frontDeskUser)
            ->get(route('front-desk.expenses.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Expenses/Create')
                ->where('routePrefix', 'front-desk'));
    }

    private function createUserWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            [
                'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                'description' => ucfirst(str_replace('_', ' ', $roleName)) . ' test role',
                'is_active' => true,
            ]
        );

        $user = User::create([
            'first_name' => ucfirst(str_replace('_', ' ', $roleName)),
            'last_name' => 'User',
            'email' => $email,
            'password' => bcrypt('password'),
            'country' => 'Cameroon',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->attach($role->id, ['assigned_by' => $user->id]);

        return $user->fresh('roles');
    }
}