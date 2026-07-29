export type InputType = 'CHOICE' | 'NUMBER' | 'DIMENSIONS';

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

export type CatalogProduct = {
    id: number;
    shop_id: number;
    product_template_id: number;
    name_override: string | null;
    slug: string | null;
    image_url: string | null;
    description: string | null;
    is_active: boolean;
    product_template?: ProductTemplate;
    pricing_profile?: PricingProfile;
    pricing_profile_exists?: boolean;
    categories?: Category[];
};
