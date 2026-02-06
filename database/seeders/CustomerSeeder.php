<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@email.com',
                'phone' => '+1234567890',
                'address' => '123 Main Street, North Village',
                'is_active' => true,
                'notes' => 'Regular customer, pays on time',
                'created_at' => now()->subMonths(6),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@email.com',
                'phone' => '+1234567891',
                'address' => '456 Oak Avenue, South Village',
                'is_active' => true,
                'notes' => 'Business customer - coffee shop owner',
                'created_at' => now()->subMonths(5),
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@email.com',
                'phone' => '+1234567892',
                'address' => '789 Pine Road, Market District',
                'is_active' => true,
                'notes' => null,
                'created_at' => now()->subMonths(4),
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@email.com',
                'phone' => '+1234567893',
                'address' => '321 Elm Street, North Village',
                'is_active' => true,
                'notes' => 'Prefers monthly plans',
                'created_at' => now()->subMonths(3),
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@email.com',
                'phone' => '+1234567894',
                'address' => '654 Maple Lane, South Village',
                'is_active' => true,
                'notes' => null,
                'created_at' => now()->subMonths(3),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@email.com',
                'phone' => '+1234567895',
                'address' => '987 Birch Court, Market District',
                'is_active' => true,
                'notes' => 'Student - uses student special plan',
                'created_at' => now()->subMonths(2),
            ],
            [
                'name' => 'Robert Miller',
                'email' => 'robert.miller@email.com',
                'phone' => '+1234567896',
                'address' => '147 Cedar Drive, North Village',
                'is_active' => true,
                'notes' => 'Family plan customer',
                'created_at' => now()->subMonths(2),
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@email.com',
                'phone' => '+1234567897',
                'address' => '258 Spruce Street, South Village',
                'is_active' => true,
                'notes' => null,
                'created_at' => now()->subMonths(1),
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@email.com',
                'phone' => '+1234567898',
                'address' => '369 Willow Road, Market District',
                'is_active' => true,
                'notes' => 'Business premium customer',
                'created_at' => now()->subMonths(1),
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@email.com',
                'phone' => '+1234567899',
                'address' => '741 Ash Avenue, North Village',
                'is_active' => true,
                'notes' => null,
                'created_at' => now()->subWeeks(3),
            ],
            [
                'name' => 'Thomas Martinez',
                'email' => 'thomas.martinez@email.com',
                'phone' => '+1234567800',
                'address' => '852 Poplar Lane, South Village',
                'is_active' => true,
                'notes' => 'New customer',
                'created_at' => now()->subWeeks(2),
            ],
            [
                'name' => 'Jennifer Taylor',
                'email' => 'jennifer.taylor@email.com',
                'phone' => '+1234567801',
                'address' => '963 Hickory Court, Market District',
                'is_active' => true,
                'notes' => 'Referred by Jane Smith',
                'created_at' => now()->subWeek(),
            ],
            [
                'name' => 'Christopher Lee',
                'email' => 'chris.lee@email.com',
                'phone' => '+1234567802',
                'address' => '159 Walnut Drive, North Village',
                'is_active' => true,
                'notes' => null,
                'created_at' => now()->subDays(5),
            ],
            [
                'name' => 'Amanda White',
                'email' => 'amanda.white@email.com',
                'phone' => '+1234567803',
                'address' => '753 Chestnut Street, South Village',
                'is_active' => true,
                'notes' => 'Prefers hourly plans',
                'created_at' => now()->subDays(3),
            ],
            [
                'name' => 'Daniel Harris',
                'email' => 'daniel.harris@email.com',
                'phone' => '+1234567804',
                'address' => '951 Sycamore Road, Market District',
                'is_active' => false,
                'notes' => 'Moved away - inactive',
                'created_at' => now()->subMonths(8),
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('Created ' . count($customers) . ' customers');
    }
}
