export type InputType = 'CHOICE' | 'NUMBER' | 'DIMENSIONS';

export type Page = {
    id: number;
    shop_id: number;
    title: string;
    slug: string;
    content: string | null;
    is_published: boolean;
};

export type Shop = {
    id: number;
    name: string;
    slug: string;
    currency: string;
    pickup_line1: string | null;
    pickup_line2: string | null;
    pickup_city: string | null;
    pickup_state: string | null;
    pickup_postal_code: string | null;
    pickup_phone: string | null;
    logo_url: string | null;
    brand_color: string | null;
    accent_color: string | null;
    contact_email: string | null;
    facebook_url: string | null;
    instagram_url: string | null;
    whatsapp_url: string | null;
};

export type Category = {
    id: number;
    shop_id: number;
    name: string;
    slug: string;
    catalog_products_count?: number;
};

export type ComponentOption = {
    id: number;
    component_id: number;
    value: string;
    label: string;
    sort_order: number;
    image_url: string | null;
};

export type Component = {
    id: number;
    code: string;
    label: string;
    input_type: InputType;
    options_count?: number;
    options?: ComponentOption[];
};

export type PricingStrategy =
    'PER_UNIT_TIERED' | 'PER_AREA' | 'PER_AREA_WITH_SETUP';

export type AttachedComponent = Component & {
    pivot: {
        sort_order: number;
        is_required: boolean;
    };
};

export type ProductTemplate = {
    id: number;
    code: string;
    name: string;
    pricing_strategy: PricingStrategy;
    components_count?: number;
    components?: AttachedComponent[];
};

export type PricingTier = {
    id: number;
    pricing_profile_id: number;
    min_quantity: number;
    max_quantity: number | null;
    unit_price: string;
    adjustment_percent: string | null;
};

export type ModifierType = 'FIXED_ADD' | 'PERCENT_MULTIPLY' | 'PER_UNIT_ADD';

export type OptionPriceModifier = {
    id: number;
    pricing_profile_id: number;
    component_option_id: number;
    modifier_type: ModifierType;
    value: string;
    component_option?: ComponentOption & { component?: Component };
};

export type PricingProfile = {
    id: number;
    catalog_product_id: number;
    params: Record<string, string> | null;
    tiers?: PricingTier[];
    option_modifiers?: OptionPriceModifier[];
};

export type CatalogProductFaq = {
    id: number;
    catalog_product_id: number;
    question: string;
    answer: string;
    sort_order: number;
};

export type CatalogProductReview = {
    id: number;
    catalog_product_id: number;
    author_name: string;
    rating: number;
    comment: string;
    sort_order: number;
};

export type CatalogProduct = {
    id: number;
    shop_id: number;
    product_template_id: number;
    name_override: string | null;
    slug: string | null;
    image_url: string | null;
    description: string | null;
    details_content: string | null;
    is_active: boolean;
    is_featured: boolean;
    product_template?: ProductTemplate;
    pricing_profile?: PricingProfile;
    pricing_profile_exists?: boolean;
    categories?: Category[];
    faqs?: CatalogProductFaq[];
    reviews?: CatalogProductReview[];
};
