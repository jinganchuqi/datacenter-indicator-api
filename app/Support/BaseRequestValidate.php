<?php


namespace App\Support;


use App\Constant\ErrorCode;
use App\Exception\ValidateException;
use App\Support\Trait\BindProp;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use WLib\Exception\AppException;
use WLib\WLog;

/**
 * Time: 2023/7/11 23:41
 * Description: 基础验证
 */
abstract class BaseRequestValidate
{
    use BindProp;

    protected RequestInterface $request;

    /**
     * @var array
     */
    private array $attributes = [];

    /**
     * @var array
     */
    public array $_before_inputs = [];

    /**
     * 验证规则
     * @return array
     */
    abstract protected function rules(): array;


    /**
     * 定义错误码
     * @return array
     */
    abstract protected function errors(): array;


    public function __construct(array $data = [])
    {
    }

    /**
     * 错误消息
     * @return array
     */
    protected function messages(): array
    {
        $messages = [];
        foreach ($this->errors() as $name => $code) {
            if (is_int($code)) {
                $messages[$name] = "_{$code}";
            } else {
                $messages[$name] = $code;
            }
        }
        return $messages;
    }

    /**
     * @param array $inputs
     * @param bool $filter
     * @return array
     * @throws AppException
     * @throws ValidateException
     */
    public function validate(array $inputs = [], bool $filter = true): array
    {
        $inputs = !empty($this->_before_inputs) ? array_merge($inputs, $this->_before_inputs) : $inputs;
        $inputs = $this->before($inputs);
        $this->validateData($inputs, $this->rules(), $this->messages());

        if ($filter) {
            $inputs = $this->filter($inputs);
        }

        $attributes = $this->callback($inputs);
        $this->bindToProp($attributes);
        $this->attributes = $attributes;

        return $this->attributes;
    }

    /**
     * 单个数据验证
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return $this
     * @throws AppException
     * @throws ValidateException
     */
    public function validateData(array $data, array $rules, array $messages = [], array $customAttributes = []): self
    {
        try {
            $validator = ApplicationContext::getContainer()
                ->get(ValidatorFactoryInterface::class)
                ->make($data, $rules, $messages, $customAttributes);

            if ($validator->fails()) {

                $error = $validator->errors()->first();
                if (str_starts_with($error, '_')) {
                    $code = str_replace('_', '', $error);
                    $error = "";
                } else {
                    $code = ErrorCode::PARAM_ERROR;
                }

                $code = is_numeric($code) ? $code : ErrorCode::PARAM_ERROR;
                $message = transErrorCode($code);
                if (\Hyperf\Config\config('app_env') == "dev" && !empty($error)) {
                    $message = "{$error}";
                }

                throw new ValidateException("{$message}", $code);
            }

        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $error = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ];
            WLog::error("系统错误", $error);
            throw new AppException("系统错误");
        }

        return $this;
    }

    /**
     * 获取当前验证通过的参数
     * @return array
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function bindToProp(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        foreach ($data as $key => $val) {
            if (!property_exists($this, $key)) {
                continue;
            }
            if (str_starts_with($key, "_")) {
                continue;
            }
            $this->{$key} = $val;
            $this->_prop[$key] = $val;
            $this->attributes[$key] = $val;
        }

        return true;
    }


    /**
     *  获取所有输入参数
     * @param      $key
     * @param null $default
     * @return array|mixed|string|null
     */
    public function input($key = null, $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    /**
     * @param array $inputs
     * @return array
     */
    protected function filter(array $inputs): array
    {
        $payload = [];
        $rules = $this->rules();

        array_walk($rules, function ($_, $key) use ($inputs, &$payload) {
            $payload[$key] = get_value($inputs, $key, null);
        });

        return $payload;
    }


    /**
     * @param array $inputs
     * @return array
     */
    protected function before(array $inputs): array
    {
        return $inputs;
    }

    /**
     * 验证通过的回调
     * @param array $payload
     * @return array
     */
    protected function callback(array $payload): array
    {
        return $payload;
    }
}
