<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PlaylistMergerCronjobs Model
 *
 * @property \App\Model\Table\PlaylistMergerTable&\Cake\ORM\Association\BelongsTo $PlaylistMerger
 *
 * @method \App\Model\Entity\PlaylistMergerCronjob newEmptyEntity()
 * @method \App\Model\Entity\PlaylistMergerCronjob newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\PlaylistMergerCronjob> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PlaylistMergerCronjob get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PlaylistMergerCronjob findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PlaylistMergerCronjob patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\PlaylistMergerCronjob> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PlaylistMergerCronjob|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PlaylistMergerCronjob saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMergerCronjob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMergerCronjob>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMergerCronjob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMergerCronjob> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMergerCronjob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMergerCronjob>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMergerCronjob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMergerCronjob> deleteManyOrFail(iterable $entities, array $options = [])
 */
class PlaylistMergerCronjobsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('playlist_merger_cronjobs');
        $this->setDisplayField('frequency');
        $this->setPrimaryKey('id');

        $this->belongsTo('PlaylistMerger', [
            'foreignKey' => 'playlist_merger_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('playlist_merger_id')
            ->notEmptyString('playlist_merger_id');

        $validator
            ->scalar('frequency')
            ->notEmptyString('frequency')
            ->inList('frequency', ['once_daily', 'twice_daily', 'four_times_daily', 'weekly']);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['playlist_merger_id'], 'PlaylistMerger'), ['errorField' => 'playlist_merger_id']);

        return $rules;
    }
}
