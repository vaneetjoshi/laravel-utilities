<?php

namespace Vaneetjoshi\LaravelUtilities\Widgets\Enums;

/**
 * Standard Widget Icons
 * 
 * Pre-defined Iconify icon names. 
 * The system will first check for a local SVG equivalent in `resources/views/components/icons/`.
 * If not found, it falls back to the Iconify library.
 */
enum WidgetIcon: string
{
    // ==========================================
    // CRYPTOCURRENCY ICONS
    // ==========================================
    case CRYPTO_BTC = 'cryptocurrency-color:btc';
    case CRYPTO_ETH = 'cryptocurrency-color:eth';
    case CRYPTO_USDT = 'cryptocurrency-color:usdt';
    case CRYPTO_BNB = 'cryptocurrency-color:bnb';
    case CRYPTO_XRP = 'cryptocurrency-color:xrp';
    case CRYPTO_SOL = 'cryptocurrency-color:sol';
    case CRYPTO_TRX = 'cryptocurrency-color:trx';
    case CRYPTO_MATIC = 'cryptocurrency-color:matic';
    
    // ==========================================
    // FIAT CURRENCY ICONS
    // ==========================================
    case FIAT_USD = 'tabler:currency-dollar';
    case FIAT_EUR = 'tabler:currency-euro';
    case FIAT_GBP = 'tabler:currency-pound';
    case FIAT_INR = 'tabler:currency-rupee';
    case FIAT_YEN = 'tabler:currency-yen';
    case FIAT_DIRHAM = 'tabler:currency-dirham';
    case FIAT_REAL = 'tabler:currency-real';
    
    // ==========================================
    // MLM & BUSINESS ICONS
    // ==========================================
    case MLM_NETWORK = 'tabler:binary-tree';
    case MLM_TEAM = 'tabler:users-group';
    case MLM_REFERRAL = 'tabler:affiliate';
    case MLM_RANK = 'tabler:medal';
    case MLM_COMMISSION = 'tabler:cash-banknote';
    case MLM_BONUS = 'tabler:gift';
    case MLM_GENEALOGY = 'tabler:hierarchy';
    case MLM_PIN = 'tabler:key';
    case MLM_WITHDRAWAL = 'tabler:wallet';
    case MLM_WALLET_ADD = 'tabler:wallet-add';
    
    // ==========================================
    // E-COMMERCE ICONS
    // ==========================================
    case ECOM_CART = 'tabler:shopping-cart';
    case ECOM_STORE = 'tabler:store';
    case ECOM_ORDER = 'tabler:package';
    case ECOM_PRODUCT = 'tabler:box';
    case ECOM_DISCOUNT = 'tabler:discount';
    case ECOM_INVOICE = 'tabler:receipt';
    case ECOM_SHIPPING = 'tabler:truck-delivery';
    case ECOM_CATEGORY = 'tabler:category';
    case ECOM_PAYMENT = 'tabler:credit-card';
    
    // ==========================================
    // DASHBOARD & APP ICONS
    // ==========================================
    case APP_DASHBOARD = 'tabler:layout-dashboard';
    case APP_SETTINGS = 'tabler:settings';
    case APP_USER = 'tabler:user';
    case APP_ANALYTICS = 'tabler:chart-infographic';
    case APP_MESSAGES = 'tabler:messages';
    case APP_NOTIFICATIONS = 'tabler:bell';
    case APP_SECURITY = 'tabler:shield-lock';
    case APP_SUPPORT = 'tabler:help-hexagon';
    case APP_ACTIVITY = 'tabler:activity';
    case APP_CALENDAR = 'tabler:calendar';
    case APP_TREND_UP = 'tabler:trending-up';
    case APP_TREND_DOWN = 'tabler:trending-down';
}