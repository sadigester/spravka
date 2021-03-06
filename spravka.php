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
        $this->version = '1.0.1';
        $this->author = 'Стойчоv';
        $this->need_instance = 0;

        parent::__construct();

        $this->default_sort_column = 'id_order';
        $this->default_sort_direction = 'ASC';
        $this->empty_message = $this->trans('An empty record-set was returned.', array(), 'Modules.Statsbestproducts.Admin');
        $this->paging_message = $this->trans('Displaying %1$s of %2$s', array('{0} - {1}', '{2}'), 'Admin.Global');

        $this->columns = array(
            array(
                'id' => 'id_order',
                'header' => $this->trans('id_order', array(), 'Admin.Global'),
                'dataIndex' => 'id_order',
                'align' => 'right'
            ),
            array(
                'id' => 'customer',
                'header' => $this->trans('Customer', array(), 'Admin.Global'),
                'dataIndex' => 'customer',
                'align' => 'left'
            ),
            array(
                'id' => 'invoice_date',
                'header' => $this->l('Дата'),
                'dataIndex' => 'invoice_date',
                'align' => 'left'
            ),
            array(
                'id' => 'product_id',
                'header' => $this->trans('product_id', array(), 'Admin.Global'),
                'dataIndex' => 'product_id',
                'align' => 'right'
            ),            
			array(
                'id' => 'product_reference',
                'header' => $this->l('Референция'),
                'dataIndex' => 'product_reference',
                'align' => 'left'
            ),
			array(
                'id' => 'product_category',
                'header' => $this->l('Категория'),
                'dataIndex' => 'product_category',
                'align' => 'left'
            ),
			array(
                'id' => 'product_name',
                'header' => $this->trans('Артикул', array(), 'Admin.Global'),
                'dataIndex' => 'product_name',
                'align' => 'left'
            ),
            /*
            array(
                'id' => 'reduction_name',
                'header' => $this->l('Отстъпка'),
                'dataIndex' => 'reduction_name',
                'align' => 'left'
            ),
            array(
                'id' => 'reduction_percent',
                'header' => $this->l('%'),
                'dataIndex' => 'reduction_percent',
                'align' => 'right'
            ),
            */
            /*
            array(
                'id' => 'group_reduction',
                'header' => $this->l('% гр'),
                'dataIndex' => 'group_reduction',
                'align' => 'right'
            ),
            array(
                'id' => 'reduction_percent',
                'header' => $this->l('% пор'),
                'dataIndex' => 'reduction_percent',
                'align' => 'right'
            ),
            */
			array(
                'id' => 'product_quantity',
                'header' => $this->l('Кол.'),
                'dataIndex' => 'product_quantity',
                'align' => 'right'
            ),			
            /*
            array(
                'id' => 'original_product_price_no_disc',
                'header' => $this->l('Кат.цена'),
                'dataIndex' => 'original_product_price_no_disc',
                'align' => 'right'
            ),
            array(
                'id' => 'unit_price_tax_excl',
                'header' => $this->l('Ед.цена'),
                'dataIndex' => 'unit_price_tax_excl',
                'align' => 'right'
            ),
            */
            array(
                'id' => 'unit_price_tax_excl_after_disc',
                'header' => $this->l('Прод.цена'),
                'dataIndex' => 'unit_price_tax_excl_after_disc',
                'align' => 'right'
            ),
            /*
            array(
                'id' => 'purchase_supplier_price',
                'header' => $this->l('Дост.цена'),
                'dataIndex' => 'purchase_supplier_price',
                'align' => 'right'
            ),
            */
            array(
                'id' => 'original_wholesale_price',
                'header' => $this->l('Дост.цена'),
                'dataIndex' => 'original_wholesale_price',
                'align' => 'right'
            ),
            array(
                'id' => 'TOTAL_PROFIT',
                'header' => $this->l('ПЕЧАЛБА'),
                'dataIndex' => 'TOTAL_PROFIT',
                'align' => 'right'
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
                '.$this->l('Всички цени в справката са в ').'<strong>'.$this->l(' български лева с ДДС').'</strong> <br />

                <ul>
                    <li>'.$this->l('С достатъчно бира всичко се постига').'</li>
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

/*
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
*/

/*

$this->query = 
'SELECT SQL_CALC_FOUND_ROWS
    o.id_order,
    o.reference,
    o.payment,
    o.date_add,
    o.date_upd,
    o.total_products_wt,
    o.total_shipping,
    o.total_discounts,
    o.total_paid,
--    o.total_paid_tax_excl,
--    o.total_paid_tax_incl,
    
    o.id_currency,
    o.valid,
    
    os.deleted,
    os.hidden,
    os.paid,
    os.shipped,
    os.color,

	o.invoice_date,
    coalesce(cr.reduction_percent,0) as reduction_percent,
	ocr.name as reduction_name,
	
--    ocr.value,
--    ocr.value_tax_excl,

    od.product_id,
--    od.product_attribute_id,
    od.product_name,
    od.product_reference,
--    od.product_price,
    od.product_quantity,
    
    ROUND (od.unit_price_tax_incl,2) AS unit_price_tax_incl ,
    ROUND (od.unit_price_tax_incl * (100 - COALESCE(cr.reduction_percent,0) ) / 100,2) as unit_price_tax_incl_after_disc,
--    od.unit_price_tax_excl,
   
--    od.total_price_tax_incl,
--    od.total_price_tax_excl,

--    od.original_product_price,    
--    od.original_wholesale_price,

    ROUND (coalesce(od.purchase_supplier_price,0),2) as purchase_supplier_price,
    ROUND (
        ROUND (od.unit_price_tax_incl * (100 - coalesce(cr.reduction_percent,0)  ) / 100,2) 
        - 
        coalesce(od.purchase_supplier_price,0)
    ,2) as UNIT_PROFIT,
    
    ROUND (
        od.product_quantity * 
        (ROUND (od.unit_price_tax_incl * (100 - coalesce(cr.reduction_percent,0) ) / 100,2) 
        - 
        coalesce(od.purchase_supplier_price,0)
        ) 
    ,2) as TOTAL_PROFIT
    
FROM '._DB_PREFIX_.'orders o
JOIN '._DB_PREFIX_.'order_detail od ON od.id_order = o.id_order
LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = o.`current_state`)
LEFT JOIN `'._DB_PREFIX_.'order_cart_rule` ocr ON (ocr.id_order = o.id_order)
LEFT JOIN `'._DB_PREFIX_.'cart_rule` cr ON (ocr.id_cart_rule = cr.id_cart_rule)  
WHERE o.invoice_date BETWEEN '.$date_between.' AND o.valid = 1 
ORDER BY o.invoice_date';

*/

$this->query = 
'SELECT SQL_CALC_FOUND_ROWS
    concat(cust.firstname,char(32),cust.lastname) as customer,
    o.id_order,
    o.reference,
    o.payment,
    o.date_add,
    o.date_upd,
    o.total_products_wt,
    o.total_shipping,
    o.total_discounts,
    o.total_paid,
--    o.total_paid_tax_excl,
--    o.total_paid_tax_incl,
    
    o.id_currency,
    o.valid,
    
    os.deleted,
    os.hidden,
    os.paid,
    os.shipped,
    os.color,

	DATE (o.invoice_date) AS invoice_date,
    ROUND(coalesce(cr.reduction_percent,0)) as reduction_percent,
	ocr.name as reduction_name,
	
--    ocr.value,
--    ocr.value_tax_excl,

    od.product_id,
--    od.product_attribute_id,
    od.product_name,
    od.product_reference,
--    od.product_price,
    cat.name as product_category,
    ROUND(od.group_reduction) AS group_reduction,
    od.product_quantity,
    
    ROUND (od.unit_price_tax_incl,2) AS unit_price_tax_incl ,
    ROUND (od.unit_price_tax_incl * (100 - COALESCE(cr.reduction_percent,0) ) / 100,2) as unit_price_tax_incl_after_disc,

    ROUND (od.unit_price_tax_excl,2) AS unit_price_tax_excl ,
    ROUND (od.unit_price_tax_excl * (100 - COALESCE(cr.reduction_percent,0) ) / 100,2) as unit_price_tax_excl_after_disc,
   
--    od.total_price_tax_incl,
--    od.total_price_tax_excl,

    ROUND(od.original_product_price,2) AS original_product_price,
    ROUND(od.original_wholesale_price,2) AS original_wholesale_price,
    ROUND(od.original_product_price * ((100+od.group_reduction) / 100), 2) AS original_product_price_no_disc,
    
    /*
    ROUND (coalesce(od.purchase_supplier_price,0),2) as purchase_supplier_price,
    ROUND (
        ROUND (od.unit_price_tax_excl * (100 - coalesce(cr.reduction_percent,0)  ) / 100,2) 
        - 
        coalesce(od.purchase_supplier_price,0)
    ,2) as UNIT_PROFIT,
    */
    
    ROUND (
        od.product_quantity * 
        (ROUND (od.unit_price_tax_excl * (100 - coalesce(cr.reduction_percent,0) ) / 100,2) 
        - 
        coalesce(od.purchase_supplier_price,0)
        ) 
    ,2) as TOTAL_PROFIT
    

FROM '._DB_PREFIX_.'orders o
JOIN '._DB_PREFIX_.'order_detail od ON od.id_order = o.id_order
JOIN '._DB_PREFIX_.'product prod on prod.id_product = od.product_id
LEFT JOIN '._DB_PREFIX_.'category_lang cat on cat.id_category = prod.id_category_default AND cat.id_lang = 1
LEFT JOIN '._DB_PREFIX_.'customer cust on cust.id_customer = o.id_customer
LEFT JOIN '._DB_PREFIX_.'order_state os ON (os.`id_order_state` = o.`current_state`)
LEFT JOIN '._DB_PREFIX_.'order_cart_rule ocr ON (ocr.id_order = o.id_order)
LEFT JOIN '._DB_PREFIX_.'cart_rule cr ON (ocr.id_cart_rule = cr.id_cart_rule)  
WHERE o.invoice_date BETWEEN '.$date_between.' AND o.valid = 1 
ORDER BY o.invoice_date';


/*
        if (Validate::IsName($this->_sort)) {
            $this->query .= ' ORDER BY `'.bqSQL($this->_sort).'`';
            if (isset($this->_direction) && Validate::isSortDirection($this->_direction)) {
                $this->query .= ' '.$this->_direction;
            }
        }

        if (($this->_start === 0 || Validate::IsUnsignedInt($this->_start)) && Validate::IsUnsignedInt($this->_limit)) {
            $this->query .= ' LIMIT '.(int)$this->_start.', '.(int)$this->_limit;
        }
*/
        $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);

        foreach ($values as &$value) {

            // Static methods  Tools::displayPrice(123.45, 'EUR') and Tools::displayNumber(123.45) are depricated from v.1.7.6
            // Instead use Context::getContext()->currentLocale->formatNumber(123.45) and Context::getContext()->currentLocale->formatPrice(123.45, 'EUR');

            /* $value['basicPrice'] = Context::getContext()->currentLocale->formatNumber($value['basicPrice']);
            $value['priceSold'] = Context::getContext()->currentLocale->formatNumber($value['priceSold']);
            $value['invertido'] = Context::getContext()->currentLocale->formatNumber($value['invertido']);
            $value['totalPriceSold'] = Context::getContext()->currentLocale->formatNumber($value['totalPriceSold']);
            $value['pechalba'] = Context::getContext()->currentLocale->formatNumber($value['pechalba']); */
            
            /*

            $value['product_quantity'] = Tools::displayNumber($value['product_quantity']);
            $value['reduction_percent'] = Tools::displayNumber($value['reduction_percent']);
            
            $value['purchase_supplier_price'] = Tools::displayPrice($value['purchase_supplier_price']);
            $value['unit_price_tax_incl'] = Tools::displayPrice($value['unit_price_tax_incl']);
            $value['unit_price_tax_incl_after_disc'] = Tools::displayPrice($value['unit_price_tax_incl_after_disc']);
            $value['UNIT_PROFIT'] = Tools::displayPrice($value['UNIT_PROFIT']);
            $value['TOTAL_PROFIT'] = Tools::displayPrice($value['TOTAL_PROFIT']);
            */
        }
        unset($value);

        $this->_values = $values;
        $this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
    }
}