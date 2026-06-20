import type { ReactElement } from "react";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { render } from "@testing-library/react";

/**
 * Renders a page component wrapped in a fresh QueryClientProvider.
 * SessionProvider is intentionally NOT included here -- each test mocks
 * "next-auth/react" directly (vi.mock) since SessionProvider itself performs
 * a session fetch on mount that would need its own network mock anyway.
 */
export function renderWithQueryClient(ui: ReactElement) {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: { retry: false },
    },
  });

  return render(
    <QueryClientProvider client={queryClient}>{ui}</QueryClientProvider>
  );
}
