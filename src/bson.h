#define FFI_LIB "/opt/homebrew/lib/libbson-1.0.dylib"
#define FFI_SCOPE "libbson"

typedef struct {
   uint32_t type;
   /*< private >*/
} bson_reader_t;

typedef struct _bson_t {
   uint32_t flags;       /* Internal flags for the bson_t. */
   uint32_t len;         /* Length of BSON data. */
   uint8_t padding[120]; /* Padding for stack allocation. */
} bson_t;

extern bson_reader_t* bson_reader_new_from_data (const char *data, size_t length);
extern const bson_t* bson_reader_read (bson_reader_t *reader, bool *reached_eof);
extern const uint8_t* bson_get_data (const bson_t *bson);
