<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PlaylistMerger Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\PlaylistMerger newEmptyEntity()
 * @method \App\Model\Entity\PlaylistMerger newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\PlaylistMerger> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PlaylistMerger get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PlaylistMerger findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PlaylistMerger patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\PlaylistMerger> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PlaylistMerger|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PlaylistMerger saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMerger>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMerger>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMerger>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMerger> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMerger>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMerger>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PlaylistMerger>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PlaylistMerger> deleteManyOrFail(iterable $entities, array $options = [])
 */
class PlaylistMergerTable extends Table
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

        $this->setTable('playlist_merger');
        $this->setDisplayField('target_playlist_id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->hasOne('PlaylistMergerCronjobs',[
        'foreign_key' => 'id'
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('source_playlists')
            ->requirePresence('source_playlists', 'create')
            ->notEmptyString('source_playlists');

        $validator
            ->scalar('target_playlist_id')
            ->maxLength('target_playlist_id', 50)
            ->requirePresence('target_playlist_id', 'create')
            ->notEmptyString('target_playlist_id')
            ->add('target_playlist_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('options')
            ->allowEmptyString('options');

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
        $rules->add($rules->isUnique(['target_playlist_id']), ['errorField' => 'target_playlist_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
