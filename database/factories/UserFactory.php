<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $time = time();
       $data = [
           'username' => $this->faker->name,
           'password' => Hash::make('123456'),
           'sex' => $this->faker->randomKey([0, 1, 2]).'',
           'mobile' => '181458485'.random_int(10,99),
           'avatar' => $this->faker->imageUrl(320, 320, 'cats'),
           'role' => $this->faker->randomKey([1 ,2,3]).'',
           'register_time' => $time,
           'last_login_time' => $time+random_int(10,20),
           'last_login_ip' => $this->faker->ipv4,
           'login_time' => $time+random_int(20,50),
           'login_ip' => $this->faker->ipv4,
           'update_time' => $time
       ];

        return $data;
    }
}
