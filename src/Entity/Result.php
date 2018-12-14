<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Result
 *
 * @ORM\Table(name="results", indexes={@ORM\Index(name="FK_USER_ID_idx", columns={"user_id"})})
 * @ORM\Entity
 */
class Result implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="result", type="integer", nullable=false)
     */
    private $result;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="datetime", nullable=false)
     */
    private $time;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * Result constructor.
     *
     * @param int $result result
     * @param User $user user
     * @param \DateTime $time time
     */
    public function __construct(
        int $result = 0,
        User $user = null,
        \DateTime $time = null
    )
    {
        $this->id = 0;
        $this->result = $result;
        $this->user = $user;
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Implements __toString()
     *
     * @return string
     * @link   http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        return sprintf(
            '%3d - %3d - %22s - %s',
            $this->id,
            $this->result,
            $this->user,
            $this->time->format('Y-m-d H:i:s')
        );
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id,
            'result' => $this->result,
            'user' => $this->user,
            'time' => $this->time->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param int $result
     */
    public function setResult(int $result): void
    {
        $this->result = $result;
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getTime(): \DateTime
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     */
    public function setTime(\DateTime $time): void
    {
        $this->time = $time;
    }

}
