<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skill>
 */
class SkillFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Skill::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'PHP',
                'Laravel',
                'JavaScript',
                'React',
                'Vue.js',
                'Node.js',
                'Python',
                'Django',
                'HTML/CSS',
                'Bootstrap',
                'Tailwind CSS',
                'MySQL',
                'PostgreSQL',
                'MongoDB',
                'Git',
                'Docker',
                'AWS',
                'Photoshop',
                'Illustrator',
                'Figma',
                'WordPress',
                'SEO',
                'Google Ads',
                'Facebook Ads',
                'Content Marketing',
                'Copywriting',
                'Translation',
                'Project Management'
            ]),
            'description' => fake()->sentence(),
        ];
    }
}