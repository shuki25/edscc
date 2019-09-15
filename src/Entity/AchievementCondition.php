<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AchievementConditionRepository")
 */
class AchievementCondition
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AchievementRule", inversedBy="achievementConditions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $achievement_rule;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $variable;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $operator;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $condition_value;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $data_type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAchievementRule(): ?AchievementRule
    {
        return $this->achievement_rule;
    }

    public function setAchievementRule(?AchievementRule $achievement_rule): self
    {
        $this->achievement_rule = $achievement_rule;

        return $this;
    }

    public function getVariable(): ?string
    {
        return $this->variable;
    }

    public function setVariable(string $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getConditionValue(): ?string
    {
        return $this->condition_value;
    }

    public function setConditionValue(string $condition_value): self
    {
        $this->condition_value = $condition_value;

        return $this;
    }

    public function getDataType(): ?string
    {
        return $this->data_type;
    }

    public function setDataType(string $data_type): self
    {
        $this->data_type = $data_type;

        return $this;
    }
}
