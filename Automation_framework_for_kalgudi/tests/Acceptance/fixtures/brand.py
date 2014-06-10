from lib.api_lib import *

faker = Factory.create()

BRAND_NAME = faker.word() + generate_random_string()
BRAND_NAME = BRAND_NAME.translate(None,",!.;@#'?$%^&*()-~`")
PAGE_TITLE = faker.word()
META_KEYWORD = faker.word()
META_DESC = faker.word()
SEARCH_KEYWORD = faker.word()
UPDATED_BRAND_NAME = faker.word() + generate_random_string()
UPDATED_BRAND_NAME = UPDATED_BRAND_NAME.translate(None,",!.;@#'?$%^&*()-~`")
UPDATED_PAGE_TITLE = faker.word()
UPDATED_META_KEYWORD = faker.word()
UPDATED_META_DESC = faker.word()
UPDATED_SEARCH_KEYWORD = faker.word()


post_payload = {
            'name': BRAND_NAME,
            'page_title': PAGE_TITLE,
            'meta_keywords': META_KEYWORD,
            'meta_description': META_DESC,
            'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/300px-England_vs_South_Africa.jpg",
            'search_keywords': SEARCH_KEYWORD
        }

put_payload = {
            'name': UPDATED_BRAND_NAME,
            'page_title': UPDATED_PAGE_TITLE,
            'meta_keywords': UPDATED_META_KEYWORD,
            'meta_description': UPDATED_META_DESC,
            'search_keywords': UPDATED_SEARCH_KEYWORD
        }

invalid_name_payload = {
                          'page_title': PAGE_TITLE,
                          'meta_keywords': META_KEYWORD,
                          'meta_description': META_DESC,
                          'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/300px-England_vs_South_Africa.jpg",
                          'search_keywords': SEARCH_KEYWORD
                        }

invalid_image_payload = {
                           'name': BRAND_NAME,
                           'page_title': PAGE_TITLE,
                           'meta_keywords': META_KEYWORD,
                           'meta_description': META_DESC,
                           'image_file': "upload.wikimedia.org/wikipedia/123/#$234",
                           'search_keywords': SEARCH_KEYWORD
                        }
