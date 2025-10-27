<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Entity;

use Doctrine\ORM\Mapping as ORM;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Price;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\OtherValue\Age;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\Amount;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\Count;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\FirstName;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\IsActive;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\IsExpired;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\LastName;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\SuccessRate;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\UserId;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\ValueWithoutGenericType\HeightInCm;

/**
 * @SuppressWarnings("PHPMD.ExcessiveParameterList")
 */
#[ORM\Entity]
#[ORM\Table(name: 'foo')]
#[ORM\UniqueConstraint(name: 'name_unique_idx', columns: ['firstName', 'lastName'])]
#[ORM\Index(name: 'is_active_idx', fields: ['isActive'])]
// phpcs:ignore SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
class Foo
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UserId::class)]
        public UserId $userId,
        #[ORM\Column(type: FirstName::class)]
        public FirstName $firstName,
        #[ORM\Column(type: LastName::class)]
        public LastName $lastName,
        #[ORM\Column(type: IsActive::class)]
        public IsActive $isActive,
        #[ORM\Column(type: IsExpired::class)]
        public IsExpired $isExpired,
        #[ORM\Column(type: Amount::class)]
        public Amount $amount,
        #[ORM\Column(type: Count::class)]
        public Count $count,
        #[ORM\Column(type: SuccessRate::class)]
        public SuccessRate $successRate,
        #[ORM\Column(type: Age::class)]
        public Age $age,
        #[ORM\Column(type: HeightInCm::class)]
        public HeightInCm $heightInCm,
        #[ORM\Column(type: Price::class)]
        public Price $price,
    ) {
    }
}
