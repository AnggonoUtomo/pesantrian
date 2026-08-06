import { Spinner } from '@/components/ui/spinner';
import { Button } from '@/components/ui/button';

type LoadingButtonProps = React.ComponentProps<typeof Button> & {
    loading?: boolean;
};

function LoadingButton({ children, loading = false, disabled, ...props }: LoadingButtonProps) {
    return (
        <Button disabled={disabled || loading} {...props}>
            {loading ? <Spinner aria-hidden="true" /> : null}
            {children}
        </Button>
    );
}

export { LoadingButton };
