<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PolicyPageController extends Controller
{
    /**
     * Render a customer policy page.
     *
     * Approved owner copy can be supplied in the newest app_config row under:
     * policy_pages.{slug}.en / policy_pages.{slug}.ar
     * Values are treated as plain text and escaped in the view.
     */
    public function show(Request $request, string $page)
    {
        $locale = $request->session()->get('locale') === 'ar' ? 'ar' : 'en';
        $pages = $this->pages($locale);

        abort_unless(array_key_exists($page, $pages), 404);

        $config = AppConfig::query()->latest('id')->value('config_json') ?? [];
        $configuredCopy = Arr::get($config, "policy_pages.{$page}.{$locale}")
            ?? Arr::get($config, "policy_pages.{$page}.en");

        $copy = is_string($configuredCopy) && trim($configuredCopy) !== ''
            ? trim($configuredCopy)
            : $pages[$page]['fallback'];

        return view('web.policy-page', [
            'pageKey' => $page,
            'page' => $pages[$page],
            'copy' => $copy,
            'isPolicyDraft' => ! (is_string($configuredCopy) && trim($configuredCopy) !== ''),
            'isAr' => $locale === 'ar',
        ]);
    }

    private function pages(string $locale): array
    {
        $isAr = $locale === 'ar';

        return [
            'privacy' => [
                'title' => $isAr ? 'سياسة الخصوصية' : 'Privacy Policy',
                'summary' => $isAr ? 'اعرف إزاي بنحافظ على بياناتك.' : 'Learn how your information is handled.',
                'fallback' => $isAr
                    ? 'المحتوى النهائي لسياسة الخصوصية لسه محتاج اعتماد من مالك المتجر. قبل الإطلاق العام، لازم يوضح سياسة الخصوصية البيانات اللي بنجمعها، سبب استخدامها، مدة الاحتفاظ بيها، وبيانات التواصل الخاصة بالخصوصية.'
                    : 'The final privacy policy is awaiting approval from the store owner. Before public launch, it must describe the data collected, the purpose for using it, retention periods, sharing practices, and privacy contact details.',
            ],
            'terms' => [
                'title' => $isAr ? 'الشروط والأحكام' : 'Terms & Conditions',
                'summary' => $isAr ? 'شروط استخدام رامو ستور والشراء منه.' : 'The conditions for using and purchasing from Ramo Store.',
                'fallback' => $isAr
                    ? 'الشروط والأحكام النهائية لسه محتاجة اعتماد من مالك المتجر. قبل الإطلاق العام، لازم توضح قواعد الشراء، قبول الطلبات، الأسعار، الإلغاء، حل النزاعات، والقانون المطبق.'
                    : 'The final terms and conditions are awaiting approval from the store owner. Before public launch, they must cover ordering, acceptance, pricing, cancellations, disputes, and the governing law.',
            ],
            'shipping-policy' => [
                'title' => $isAr ? 'سياسة الشحن والتوصيل' : 'Shipping & Delivery Policy',
                'summary' => $isAr ? 'معلومات التوصيل والرسوم والمناطق المتاحة.' : 'Delivery coverage, fees, and timing information.',
                'fallback' => $isAr
                    ? 'سياسة الشحن والتوصيل النهائية لسه محتاجة اعتماد من مالك المتجر. الرسوم المتاحة بتظهر في ملخص الطلب، لكن لازم يتنشر قبل الإطلاق العام نطاق التوصيل، المواعيد المتوقعة، الاستثناءات، وطريقة متابعة الطلب.'
                    : 'The final shipping and delivery policy is awaiting approval from the store owner. Available fees appear in the order summary, but public launch requires published delivery coverage, expected timing, exceptions, and order-tracking guidance.',
            ],
            'returns-policy' => [
                'title' => $isAr ? 'سياسة الاسترجاع والاستبدال' : 'Returns & Exchanges Policy',
                'summary' => $isAr ? 'اعرف شروط وخطوات طلب الاسترجاع أو الاستبدال.' : 'Learn the conditions and steps for returns or exchanges.',
                'fallback' => $isAr
                    ? 'سياسة الاسترجاع والاستبدال النهائية لسه محتاجة اعتماد من مالك المتجر. قبل الإطلاق العام، لازم توضح المدة المسموح بيها، حالة المنتج المطلوبة، الاستثناءات، الرسوم إن وجدت، وخطوات طلب الاسترجاع أو الاستبدال.'
                    : 'The final returns and exchanges policy is awaiting approval from the store owner. Before public launch, it must state the eligibility period, required item condition, exclusions, any fees, and the steps for making a request.',
            ],
            'contact' => [
                'title' => $isAr ? 'تواصل معانا' : 'Contact Us',
                'summary' => $isAr ? 'طرق التواصل الرسمية لخدمة العملاء.' : 'Official ways to reach customer support.',
                'fallback' => $isAr
                    ? 'بيانات التواصل الرسمية لخدمة العملاء لسه محتاجة اعتماد من مالك المتجر. قبل الإطلاق العام، لازم يتنشر بريد أو رقم دعم فعّال، ساعات العمل، وطريقة متابعة الطلبات أو الشكاوى.'
                    : 'Official customer-support details are awaiting approval from the store owner. Before public launch, publish an active support email or phone number, working hours, and a way to follow up on orders or complaints.',
            ],
            'payment-info' => [
                'title' => $isAr ? 'معلومات الدفع' : 'Payment Information',
                'summary' => $isAr ? 'معلومات طرق الدفع المتاحة وتأمين العملية.' : 'Information about available payment methods and secure checkout.',
                'fallback' => $isAr
                    ? 'طرق الدفع المتاحة بتظهر وقت إتمام الطلب. قبل الإطلاق العام، لازم يعتمد مالك المتجر المحتوى النهائي اللي يوضح مزوّدي الدفع، مواعيد تأكيد الدفع، وطريقة التعامل مع الإيصالات أو المدفوعات المعلّقة.'
                    : 'Available payment methods are shown at checkout. Before public launch, the store owner must approve final content explaining payment providers, confirmation timing, and how receipts or pending payments are handled.',
            ],
        ];
    }
}
