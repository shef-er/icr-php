<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity;

/**
 * @Entity @Table(name="actors")
 * @InheritanceType("JOINED")
 */
class Actor extends Entity
{
    /**
     * @Id
     * @Column(type="guid")
     * @GeneratedValue(strategy="UUID")
     * @var string
     */
    protected $id;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $full_name;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $role;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->full_name;
    }

    /**
     * @param string
     */
    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }

    /**
     * @return string
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return self::selfSerialize();
    }

    /**
     * @return array
     */
    public function selfSerialize(bool $withId = true): array
    {
        $props = [
            'full_name' => $this->full_name,
            'role'      => $this->role,
        ];

        if($withId) {
            $props['id'] = $this->id;
        }

        return $props;
    }

}