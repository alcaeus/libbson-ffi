#define FFI_LIB "/opt/homebrew/lib/libbson-1.0.dylib"
#define FFI_SCOPE "libbson"

/** From bson-types.h */
typedef struct {
   uint32_t type;
   /*< private >*/
} bson_reader_t;

typedef struct _bson_t {
   uint32_t flags;       /* Internal flags for the bson_t. */
   uint32_t len;         /* Length of BSON data. */
   uint8_t padding[120]; /* Padding for stack allocation. */
} bson_t;

typedef struct {
   uint8_t bytes[12];
} bson_oid_t;

typedef struct {
   // TODO: Figure out little endian vs. big endian
   uint64_t low;
   uint64_t high;
#endif
} bson_decimal128_t;

typedef enum {
   BSON_TYPE_EOD = 0x00,
   BSON_TYPE_DOUBLE = 0x01,
   BSON_TYPE_UTF8 = 0x02,
   BSON_TYPE_DOCUMENT = 0x03,
   BSON_TYPE_ARRAY = 0x04,
   BSON_TYPE_BINARY = 0x05,
   BSON_TYPE_UNDEFINED = 0x06,
   BSON_TYPE_OID = 0x07,
   BSON_TYPE_BOOL = 0x08,
   BSON_TYPE_DATE_TIME = 0x09,
   BSON_TYPE_NULL = 0x0A,
   BSON_TYPE_REGEX = 0x0B,
   BSON_TYPE_DBPOINTER = 0x0C,
   BSON_TYPE_CODE = 0x0D,
   BSON_TYPE_SYMBOL = 0x0E,
   BSON_TYPE_CODEWSCOPE = 0x0F,
   BSON_TYPE_INT32 = 0x10,
   BSON_TYPE_TIMESTAMP = 0x11,
   BSON_TYPE_INT64 = 0x12,
   BSON_TYPE_DECIMAL128 = 0x13,
   BSON_TYPE_MAXKEY = 0x7F,
   BSON_TYPE_MINKEY = 0xFF,
} bson_type_t;

typedef enum {
   BSON_SUBTYPE_BINARY = 0x00,
   BSON_SUBTYPE_FUNCTION = 0x01,
   BSON_SUBTYPE_BINARY_DEPRECATED = 0x02,
   BSON_SUBTYPE_UUID_DEPRECATED = 0x03,
   BSON_SUBTYPE_UUID = 0x04,
   BSON_SUBTYPE_MD5 = 0x05,
   BSON_SUBTYPE_ENCRYPTED = 0x06,
   BSON_SUBTYPE_COLUMN = 0x07,
   BSON_SUBTYPE_USER = 0x80,
} bson_subtype_t;

typedef struct _bson_value_t {
   bson_type_t value_type;
   int32_t padding;
   union {
      bson_oid_t v_oid;
      int64_t v_int64;
      int32_t v_int32;
      int8_t v_int8;
      double v_double;
      bool v_bool;
      int64_t v_datetime;
      struct {
         uint32_t timestamp;
         uint32_t increment;
      } v_timestamp;
      struct {
         char *str;
         uint32_t len;
      } v_utf8;
      struct {
         uint8_t *data;
         uint32_t data_len;
      } v_doc;
      struct {
         uint8_t *data;
         uint32_t data_len;
         bson_subtype_t subtype;
      } v_binary;
      struct {
         char *regex;
         char *options;
      } v_regex;
      struct {
         char *collection;
         uint32_t collection_len;
         bson_oid_t oid;
      } v_dbpointer;
      struct {
         char *code;
         uint32_t code_len;
      } v_code;
      struct {
         char *code;
         uint8_t *scope_data;
         uint32_t code_len;
         uint32_t scope_len;
      } v_codewscope;
      struct {
         char *symbol;
         uint32_t len;
      } v_symbol;
      bson_decimal128_t v_decimal128;
   } value;
} bson_value_t;

typedef struct {
   const uint8_t *raw; /* The raw buffer being iterated. */
   uint32_t len;       /* The length of raw. */
   uint32_t off;       /* The offset within the buffer. */
   uint32_t type;      /* The offset of the type byte. */
   uint32_t key;       /* The offset of the key byte. */
   uint32_t d1;        /* The offset of the first data byte. */
   uint32_t d2;        /* The offset of the second data byte. */
   uint32_t d3;        /* The offset of the third data byte. */
   uint32_t d4;        /* The offset of the fourth data byte. */
   uint32_t next_off;  /* The offset of the next field. */
   uint32_t err_off;   /* The offset of the error. */
   bson_value_t value; /* Internal value for various state. */
} bson_iter_t;

/** From bson.h */
extern bson_reader_t* bson_reader_new_from_data (const char *data, size_t length);
extern const bson_t* bson_reader_read (bson_reader_t *reader, bool *reached_eof);
extern bson_t* bson_new_from_data(const uint8_t *data, size_t length);
extern bson_t* bson_copy (const bson_t *bson);
extern const uint8_t* bson_get_data (const bson_t *bson);

/** From bson-iter.h */
extern bool bson_iter_init (bson_iter_t *iter, const bson_t *bson);
extern bool bson_iter_find (bson_iter_t *iter, const char *key);
extern bool bson_iter_find_w_len (bson_iter_t *iter, const char *key, int keylen);
extern bool bson_iter_next(bson_iter_t *iter);
extern const char* bson_iter_key (const bson_iter_t *iter);
extern bson_type_t bson_iter_type (const bson_iter_t *iter);
extern int64_t bson_iter_as_int64 (const bson_iter_t *iter);
extern const char* bson_iter_utf8 (const bson_iter_t *iter, uint32_t *length);
extern bool bson_iter_bool (const bson_iter_t *iter);
extern double bson_iter_double (const bson_iter_t *iter);
extern const bson_oid_t* bson_iter_oid (const bson_iter_t *iter);
extern void bson_iter_document (const bson_iter_t *iter, uint32_t *document_len, const uint8_t **document);

/** From bson-utf8.h */
extern bool bson_utf8_validate (const char *utf8, size_t utf8_len, bool allow_null);

/** From bson-oid.h */
extern void bson_oid_to_string (const bson_oid_t *oid, char str[25]);
