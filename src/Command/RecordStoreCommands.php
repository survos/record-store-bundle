<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Command;

use Survos\RecordStoreBundle\Model\RecordQuery;
use Survos\RecordStoreBundle\Model\RecordSort;
use Survos\RecordStoreBundle\Model\SortDirection;
use Survos\RecordStoreBundle\Registry\RecordStoreRegistry;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class RecordStoreCommands
{
    public function __construct(private RecordStoreRegistry $stores)
    {
    }

    #[AsCommand('record-store:applications', 'List configured record-store applications')]
    public function applications(SymfonyStyle $io, #[Option('Emit JSON')] bool $json = false): int
    {
        $applications = [];
        foreach ($this->stores->applicationNames() as $name) {
            $application = $this->stores->application($name);
            $adapter = $this->stores->adapterFor($application);
            $applications[] = [
                'name' => $application->name,
                'connection' => $application->connection,
                'provider' => $adapter->provider(),
                'id' => $application->id,
                'tables' => count($application->tables),
            ];
        }

        if ($json) {
            $io->writeln(json_encode($applications, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $io->title('Record-store applications');
            $io->table(['Name', 'Connection', 'Provider', 'Remote ID', 'Configured tables'], array_map('array_values', $applications));
        }

        return Command::SUCCESS;
    }

    #[AsCommand('record-store:schema', 'Inspect a record-store application schema')]
    public function schema(
        SymfonyStyle $io,
        #[Argument('Configured application name')] string $application,
        #[Option('Emit JSON')] bool $json = false,
    ): int {
        $reference = $this->stores->application($application);
        $schema = $this->stores->adapterFor($reference)->schema($reference);
        $payload = [
            'id' => $schema->id,
            'name' => $schema->name,
            'tables' => array_map(static fn ($table): array => [
                'id' => $table->id,
                'name' => $table->name,
                'label' => $table->label,
                'fields' => array_map(static fn ($field): array => [
                    'id' => $field->id,
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type->value,
                ], $table->fields),
            ], $schema->tables),
        ];

        if ($json) {
            $io->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($schema->tables as $table) {
            $rows[] = [$table->id, $table->label, count($table->fields)];
        }
        $io->title(sprintf('Record-store schema · %s', $application));
        $io->table(['Table ID', 'Label', 'Fields'], $rows);

        return Command::SUCCESS;
    }

    #[AsCommand('record-store:query', 'Query records through a provider-neutral adapter')]
    public function query(
        SymfonyStyle $io,
        #[Argument('Configured application.table name')] string $table,
        #[Option('Comma-separated logical field names')] string $select = '',
        #[Option('JSON object mapping logical fields to allowed-value arrays')] string $filter = '{}',
        #[Option('Comma-separated field:ASC|DESC sorts')] string $sort = '',
        #[Option('Maximum records')] int $limit = 20,
        #[Option('Record offset, when supported by the provider')] int $offset = 0,
        #[Option('Emit JSON')] bool $json = false,
    ): int {
        $reference = $this->stores->table($table);
        $query = new RecordQuery(
            self::csv($select),
            self::filters($filter),
            self::sorts($sort),
            $limit,
            $offset,
        );
        $page = $this->stores->adapterFor($reference)->query($reference, $query);
        $payload = array_map(static fn ($record): array => [
            'id' => $record->id,
            'fields' => $record->fields,
        ], $page->records);

        if ($json) {
            $io->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return Command::SUCCESS;
        }

        $io->title(sprintf('Record-store records · %s', $table));
        $io->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $io->success(sprintf('%d record%s.', count($page->records), 1 === count($page->records) ? '' : 's'));

        return Command::SUCCESS;
    }

    /** @return list<string> */
    private static function csv(string $value): array
    {
        if ('' === trim($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => '' !== $item));
    }

    /** @return array<string, list<int|float|string|bool|null>> */
    private static function filters(string $value): array
    {
        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('Record-store filters must be a JSON object.');
        }
        $filters = [];
        foreach ($decoded as $field => $allowed) {
            if (!is_string($field) || !is_array($allowed)) {
                throw new \InvalidArgumentException('Each record-store filter must map a field name to an array.');
            }
            $values = [];
            foreach ($allowed as $item) {
                if (!is_int($item) && !is_float($item) && !is_string($item) && !is_bool($item) && null !== $item) {
                    throw new \InvalidArgumentException('Record-store filter values must be scalar or null.');
                }
                $values[] = $item;
            }
            $filters[$field] = $values;
        }

        return $filters;
    }

    /** @return list<RecordSort> */
    private static function sorts(string $value): array
    {
        $sorts = [];
        foreach (self::csv($value) as $sort) {
            [$field, $direction] = array_pad(explode(':', $sort, 2), 2, 'ASC');
            $sorts[] = new RecordSort($field, SortDirection::from(strtoupper($direction)));
        }

        return $sorts;
    }
}
