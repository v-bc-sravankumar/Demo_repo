from lib.api_lib import *

faker = Factory.create()

CATAGORY_NAME = faker.word() + generate_random_string()
CATAGORY_NAME = CATAGORY_NAME.translate(None,",!.;@#'?$%^&*()-~`")
CATEGORY_DESCRIPTION = faker.word()
PAGE_TITLE = faker.word()
META_KEYWORD = faker.word()
META_DESC = faker.word()
SEARCH_KEYWORD = faker.word()
UPDATED_CATAGORY_NAME = faker.word() + generate_random_string()
UPDATED_CATAGORY_NAME = UPDATED_CATAGORY_NAME.translate(None,",!.;@#'?$%^&*()-~`")
UPDATED_CATEGORY_DESCRIPTION = faker.word()
UPDATED_PAGE_TITLE = faker.word()
UPDATED_META_KEYWORD = faker.word()
UPDATED_META_DESC = faker.word()
UPDATED_SEARCH_KEYWORD = faker.word()


post_payload = {
    'name': CATAGORY_NAME,
    'description': CATEGORY_DESCRIPTION,
    'sort_order': 1,
    'page_title': PAGE_TITLE,
    'meta_keywords': META_KEYWORD,
    'meta_description': META_DESC,
    'layout_file': "category.html",
    'is_visible': True,
    'search_keywords': SEARCH_KEYWORD
}

put_payload = {
    'name': UPDATED_CATAGORY_NAME,
    'description': UPDATED_CATEGORY_DESCRIPTION,
    'sort_order': 2,
    'page_title': UPDATED_PAGE_TITLE,
    'meta_keywords': UPDATED_META_KEYWORD,
    'meta_description': UPDATED_META_DESC,
    'is_visible': False,
    'search_keywords': UPDATED_SEARCH_KEYWORD
}

required_name_payload = {
        'description': CATEGORY_DESCRIPTION,
        'sort_order': 1,
        'page_title': PAGE_TITLE,
        'meta_keywords': META_KEYWORD,
        'meta_description': META_DESC,
        'layout_file': "category.html",
        'is_visible': True,
        'search_keywords': SEARCH_KEYWORD
}

invalid_sortorder_payload = {
        'name': CATAGORY_NAME,
        'description': CATEGORY_DESCRIPTION,
        'sort_order': "order",
        'page_title': PAGE_TITLE,
        'meta_keywords': META_KEYWORD,
        'meta_description': META_DESC,
        'layout_file': "category.html",
        'is_visible': True,
        'search_keywords': SEARCH_KEYWORD
}

invalid_isvisible_payload = {
        'name': CATAGORY_NAME,
        'description': CATEGORY_DESCRIPTION,
        'sort_order': 1,
        'page_title': PAGE_TITLE,
        'meta_keywords': META_KEYWORD,
        'meta_description': META_DESC,
        'layout_file': "category.html",
        'is_visible': "yes",
        'search_keywords': SEARCH_KEYWORD
}
