<?php
/**

*
* Do not edit or add to this file if you wish to upgrade Samuil Genov to newer
* versions in the future. If you wish to customize Samuil Genov for your
* needs please refer to http://www.Samuil Genov.com for more information.
*
*  @author    Samuil Genov <sadigester@gmail.com>
*  @copyright Samuil Genov 
*  
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class spravka extends ModuleGrid
{
    private $html = null;
    private $query = null;
    private $columns = null;
    private $default_sort_column = null;
    private $default_sort_direction = null;
    private $empty_message = null;
    private $paging_message = null;

    public function __construct()
    {
        $this->name = 'spravka';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Samuil Genov';
        $this->need_instance = 0;

        parent::__construct();

        $this->default_sort_column = 'totalPriceSold';
        $this->default_sort_direction = 'DESC';
        $this->empty_message = $this->trans('An empty record-set was returned.', array(), 'Modules.Statsbestproducts.Admin');
        $this->paging_message = $this->trans('Displaying %1$s of %2$s', array('{0} - {1}', '{2}'), 'Admin.Global');

        $this->columns = array(
            array(
                'id' => 'id_product',
                'header' => $this->l('Ид'),
                'dataIndex' => 'id_product',
                'align' => 'center'
            ),
            array(
                'id' => 'reference',
                'header' => $this->trans('Reference', array(), 'Admin.Global'),
                'dataIndex' => 'reference',
                'align' => 'left'
            ),
            array(
                'id' => 'name',
                'header' => $this->trans('Name', array(), 'Admin.Global'),
                'dataIndex' => 'name',
                'align' => 'left'
            ),
            array(
                'id' => 'basicPrice',
                'header' => $this->l('Покупна цена'),
                'dataIndex' => 'basicPrice',
                'align' => 'center'
            ),
            array(
                'id' => 'priceSold',
                'header' => $this->l('Прод. цена'),
                'dataIndex' => 'priceSold',
                'align' => 'center'
            ),
            array(
                'id' => 'totalQuantitySold',
                'header' => $this->l('Продадено'),
                'dataIndex' => 'totalQuantitySold',
                'align' => 'center'
            ),
            array(
                'id' => 'invertido',
                'header' => $this->l('Вложено'),
                'dataIndex' => 'invertido',
                'align' => 'center'
            ),
            array(
                'id' => 'totalPriceSold',
                'header' => $this->l('Приходи'),
                'dataIndex' => 'totalPriceSold',
                'align' => 'center'
            ),
            array(
                'id' => 'pechalba',
                'header' => $this->l('Печалба'),
                'dataIndex' => 'pechalba',
                'align' => 'center'
            )
        );

        $this->displayName = $this->l('Molinezia');
        $this->description = $this->l('Справка печалба по продукти');
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return (parent::install() && $this->registerHook('AdminStatsModules'));
    }

    public function hookAdminStatsModules($params)
    {
        $engine_params = array(
            'id' => 'id_product',
            'title' => $this->displayName,
            'columns' => $this->columns,
            'defaultSortColumn' => $this->default_sort_column,
            'defaultSortDirection' => $this->default_sort_direction,
            'emptyMessage' => $this->empty_message,
            'pagingMessage' => $this->paging_message
        );

        if (Tools::getValue('export')) {
            $this->csvExport($engine_params);
        }

        return '<div class="panel-heading">'.$this->displayName.'</div>
		'.$this->engine($engine_params).'
		<a class="btn btn-warning export-csv" href="'.Tools::safeOutput($_SERVER['REQUEST_URI'].'&export=1').'">
			<i class="icon-cloud-upload"></i> '.$this->trans('CSV Export', array(), 'Admin.Global').'
        </a>
        <h4>'.$this->l('Бележки').'</h4>
        <div class="alert alert-info">
            <h4>'.$this->l('Разяснение към модула').'</h4>
            <div>
                '.$this->l('Всички цени в справката са в ').'<strong>'.$this->l(' български лева').'</strong> <br />
                '.$this->l('Стойностите са ').'<strong>'.$this->l(' без данък').'</strong><br />
                '.$this->l('').'
                <ul>
                    <li>'.$this->l('Колона \'Вложено\' е сумата от покупната цена на продукта без данък, умножена по продаденото количество').'</li>
                    <li>'.$this->l('В колона \'Приходи\' стойността е резултат от сумата от продажните цени без данък, но с вкл. отстъпки и умножена по продаденото количество').'</li>
                    <li>'.$this->l('\'Печалба\' ни дава разликата от стойностите от колоните \'Вложено\' и \'Приходи\'').'</li>
                </ul>
            </div>
        </div>
        ';
        
    }

    public function getData()
    {
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $date_between = $this->getDate();
        $array_date_between = explode(' AND ', $date_between);

        $this->query = 'SELECT SQL_CALC_FOUND_ROWS p.reference, p.id_product, pl.name,
				ROUND(p.wholesale_price, 2) as basicPrice,
                ROUND(p.price, 2) as priceSold,
                IFNULL(SUM(od.product_quantity), 0) AS totalQuantitySold,
				ROUND(SUM(p.wholesale_price * od.product_quantity), 2) as invertido,
				ROUND(IFNULL(SUM(o.total_paid_tax_excl) * od.product_quantity, 0), 2) AS totalPriceSold,
                ROUND((SUM(o.total_paid_tax_excl)-SUM(p.wholesale_price)), 2) as pechalba,
				product_shop.active
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = '.(int)$this->getLang().' '.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN '._DB_PREFIX_.'order_detail od ON od.product_id = p.id_product
				LEFT JOIN '._DB_PREFIX_.'orders o ON od.id_order = o.id_order
				'.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o').'
				'.Product::sqlStock('p', 0).'
				WHERE o.valid = 1
				AND o.invoice_date BETWEEN '.$date_between.'
				GROUP BY od.product_id';

        if (Validate::IsName($this->_sort)) {
            $this->query .= ' ORDER BY `'.bqSQL($this->_sort).'`';
            if (isset($this->_direction) && Validate::isSortDirection($this->_direction)) {
                $this->query .= ' '.$this->_direction;
            }
        }

        if (($this->_start === 0 || Validate::IsUnsignedInt($this->_start)) && Validate::IsUnsignedInt($this->_limit)) {
            $this->query .= ' LIMIT '.(int)$this->_start.', '.(int)$this->_limit;
        }

        $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);

        foreach ($values as &$value) {

            // Static methods  Tools::displayPrice(123.45, 'EUR') and Tools::displayNumber(123.45) are depricated from v.1.7.6
            // Instead use Context::getContext()->currentLocale->formatNumber(123.45) and Context::getContext()->currentLocale->formatPrice(123.45, 'EUR');

            /* $value['basicPrice'] = Context::getContext()->currentLocale->formatNumber($value['basicPrice']);
            $value['priceSold'] = Context::getContext()->currentLocale->formatNumber($value['priceSold']);
            $value['invertido'] = Context::getContext()->currentLocale->formatNumber($value['invertido']);
            $value['totalPriceSold'] = Context::getContext()->currentLocale->formatNumber($value['totalPriceSold']);
            $value['pechalba'] = Context::getContext()->currentLocale->formatNumber($value['pechalba']); */

            $value['basicPrice'] = Tools::displayNumber($value['basicPrice']);
            $value['priceSold'] = Tools::displayNumber($value['priceSold']);
            $value['invertido'] = Tools::displayNumber($value['invertido']);
            $value['totalPriceSold'] = Tools::displayNumber($value['totalPriceSold']);
            $value['pechalba'] = Tools::displayNumber($value['pechalba']);
        }
        unset($value);

        $this->_values = $values;
        $this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
    }
}