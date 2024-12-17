<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\User> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('display_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('PlaylistMerger')    
            ->setForeignKey('user_id')
            ->setBindingKey('id');
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
            ->scalar('spotify_id')
            ->maxLength('spotify_id', 100)
            ->requirePresence('spotify_id', 'create')
            ->notEmptyString('spotify_id');

        $validator
            ->scalar('display_name')
            ->maxLength('display_name', 255)
            ->requirePresence('display_name', 'create')
            ->notEmptyString('display_name');

        $validator
            ->scalar('image_url')
            ->maxLength('image_url', 255)
            ->allowEmptyString('image_url');

        $validator
            ->scalar('access_token')
            ->maxLength('access_token', 500)
            ->allowEmptyString('access_token');

        $validator
            ->scalar('refresh_token')
            ->maxLength('refresh_token', 500)
            ->allowEmptyString('refresh_token');

        return $validator;
    }

    /**
     * Updates tokens of entity
     * @param \App\Model\Entity\User $entity
     * @param string $accessToken
     * @param string $refreshToken
     * @return \App\Model\Entity\User
     */
    public function updateUserTokens(\App\Model\Entity\User $entity, string $accessToken, string $refreshToken)
    {
        $entity = $this->patchEntity($entity, ['access_token' => $accessToken, 'refresh_token' => $refreshToken]);
        $this->save($entity);
        return $entity;
    }
}
