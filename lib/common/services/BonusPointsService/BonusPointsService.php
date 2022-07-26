<?php

declare (strict_types=1);


namespace common\services\BonusPointsService;


use backend\services\ConfigurationService;
use common\services\BonusPointsService\Clear\ClearStrategyFactory;

class BonusPointsService
{
    /** @var ClearStrategyFactory */
    private $clearStrategyFactory;
    /** @var ConfigurationService */
    private $configurationService;

    public function __construct(
        ClearStrategyFactory $clearStrategyFactory,
        ConfigurationService $configurationService
    )
    {
        $this->clearStrategyFactory = $clearStrategyFactory;
        $this->configurationService = $configurationService;
    }

    public function clearBonusPoints(string $rule, ?int $platformId = null): void
    {
        try {
            $whereRule = $this->clearStrategyFactory->createFromRule($rule)->getSQLWhere();
            if ($platformId > 0) {
                $whereRule .= " AND c.platform_id = {$platformId}";
            }
            if ($platformId === -1) {
                $whereRule .= ' AND NOT EXISTS (SELECT p.platform_id FROM platforms p WHERE c.platform_id = p.platform_id)';
            }
            \Yii::$app->db->createCommand("
                UPDATE customers c
                LEFT JOIN customers_info ci ON c.customers_id = ci.customers_info_id
                SET customers_bonus_points = 0 
                WHERE {$whereRule} 
            ")->execute();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }

    /**
     * @param int $customers_bonus_points
     * @param float $bonusPointsCosts
     * @return float
     */
    public function getAmount(int $customers_bonus_points, float $bonusPointsCosts): float
    {
        if ($bonusPointsCosts < 0) {
            throw new \RuntimeException('Wrong bonus points rate value');
        }
        return round($customers_bonus_points * $bonusPointsCosts, 2);
    }

    public function allowCustomerConvertBonusPointsToAmount(?int $platformId = null): bool
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('BonusActions')) {
            return false;
        }
        if ($platformId === null && defined('BONUS_POINT_TO_CREDIT_AMOUNT')) {
            return BONUS_POINT_TO_CREDIT_AMOUNT === 'True';
        }
        return $this->configurationService->findValue('BONUS_POINT_TO_CREDIT_AMOUNT', $platformId) === 'True';
    }

}
