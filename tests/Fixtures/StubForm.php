<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Livewire\Component;
use Sirius\LaravelBuilder\Definitions\ColumnDefinition;
use Sirius\LaravelBuilder\Definitions\FieldType;
use Sirius\LaravelBuilder\Definitions\TableDefinition;
use Sirius\LaravelBuilder\Generators\FieldRenderer;
use Sirius\LaravelBuilder\Inference\FieldPlanner;

final class StubForm extends Component
{
    /** @var array<string,string|bool|null> */
    public array $form = ['title' => '', 'price' => null, 'email' => '', 'url' => '', 'password' => '', 'password_confirmation' => '', 'phone' => null, 'number' => null, 'switch' => false, 'textarea' => '', 'richtext' => '', 'select' => null, 'date' => null, 'datetime' => null, 'time' => null];

    public ?string $submitted = null;

    public function save(): void
    {
        $this->validate(['form.title' => 'required', 'form.price' => 'required|decimal:2']);
        $this->submitted = $this->form['price'];
    }

    public function clear(): void
    {
        $this->reset('form', 'submitted');
        $this->resetValidation();
    }

    public function render(): string
    {
        $title = new ColumnDefinition('title', 'string', 'varchar', false, null, 120, null, null, false, false, false, null, null);
        $price = new ColumnDefinition('price', 'decimal', 'decimal', false, null, null, 10, 2, false, false, false, null, null);
        $table = new TableDefinition('testing', 'sqlite', 'products', 'id', [$title, $price], [], [], []);
        $renderer = new FieldRenderer;
        $planner = new FieldPlanner;
        $additional = '';
        foreach (FieldType::cases() as $type) {
            if (in_array($type, [FieldType::Text, FieldType::Currency], true)) {
                continue;
            }
            $storage = match ($type) {
                FieldType::Boolean                                  => 'boolean', FieldType::Number => 'integer',
                FieldType::Textarea,FieldType::Richtext             => 'text', FieldType::Select => 'enum',
                FieldType::Date,FieldType::Time,FieldType::Datetime => $type->value, default => 'string',
            };
            $name = $type === FieldType::Boolean ? 'switch' : $type->value;
            $column = new ColumnDefinition($name, $storage, $storage, true, null, null, null, null, false, false, false, null, null);
            $additional .= $renderer->render($planner->plan($table, $column, $type), 'product');
        }

        return '<form novalidate wire:submit="save">' . $renderer->render($planner->plan($table, $title, FieldType::Text), 'product')
            . $renderer->render($planner->plan($table, $price, FieldType::Currency), 'product')
            . $additional . '<button type="button" wire:click="save">Save</button><button type="button" wire:click="clear">Clear</button><output>{{ $submitted }}</output></form>';
    }
}
