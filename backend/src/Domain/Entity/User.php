<?php
declare(strict_types=1);

namespace App\Domain\Entity;

/**
 * @Entity @Table(name="users")
 */
class User extends Actor
{
    /**
     * @Column(type="string")
     * @var string
     */
    protected $registration_date;

    /**
     * @Column(type="json")
     * @var string
     */
    protected $credentials;

    public function __construct()
    {
        $this->credentials = '';
    }

    /**
     * @return null|string
     */
    public function getRegistrationDate(): ?string
    {
        return $this->registration_date;
    }

    /**
     * @param string $value
     */
    public function setRegistrationDate($registration_date): void
    {
        $this->registration_date = $registration_date;
    }

    /**
     * @return string
     */
    public function getCredentials(): string
    {
        return $this->credentials;
    }

    /**
     * @param string $value
     */
    public function setCredentials(string $json) {
        $this->credentials = $json;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $props = array_merge(
            parent::selfSerialize(),
            self::selfSerialize()
        );
        $props['credentials'] = json_decode($props['credentials']);
        return $props;
    }

    /**
     * @return array
     */
    public function selfSerialize(bool $withId = true): array
    {
        $props = [
            'registration_date' => $this->registration_date,
            'credentials'       => $this->credentials,
        ];
        
        if($withId) {
            $props['id'] = $this->id;
        }

        return $props;
    }

    /**
     * @return array
     */
    public function parentSerialize(bool $withId = true): array
    {
        return parent::selfSerialize($withId);
    }

    /**
     * @return string
     */
    public function getGoogleUid(): ?string
    {
        return $this->jsonRead($this->credentials, 'google_uid');
    }

    /**
     * @param string $value
     */
    public function setGoogleUid(string $value): void
    {
        $this->jsonInsert($this->credentials, 'google_uid', $value);
    }

    /**
     * @return string
     */
    public function getAvatar(): ?string
    {
        return $this->jsonRead($this->credentials, 'avatar');
    }

    /**
     * @param string $value
     */
    public function setAvatar(string $value): void
    {
        $this->jsonInsert($this->credentials, 'avatar', $value);
    }

    /**
     * @return array
     */
    public function getEmails(): array
    {
        return $this->jsonRead($this->credentials, 'emails');
    }

    /**
     * @param string $value
     * @return bool Retruns false if passed email is already exists
     */
    public function addEmail($value): bool
    {
        return $this->jsonArrayInsert($this->credentials, 'emails', $value);
    }
}