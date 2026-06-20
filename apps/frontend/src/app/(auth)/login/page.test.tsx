import { describe, it, expect, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import LoginPage from "./page";

vi.mock("next-auth/react", () => ({
  signIn: vi.fn(),
}));

vi.mock("next/navigation", () => ({
  useRouter: () => ({ push: vi.fn(), refresh: vi.fn() }),
  useSearchParams: () => new URLSearchParams(),
}));

describe("LoginPage", () => {
  it("renders without throwing and shows the sign-in form", () => {
    render(<LoginPage />);

    expect(screen.getByText("WMS Lite")).toBeTruthy();
    // NOTE: the email Label's htmlFor="email_address" does not match the
    // Input's id="email" in src/app/(auth)/login/page.tsx -- a pre-existing
    // product bug, out of this phase's blast radius (tests only, per Step C
    // scope). Querying by placeholder instead of label to avoid depending on
    // the broken association. Backlog candidate for Phase 2.
    expect(screen.getByPlaceholderText("you@example.com")).toBeTruthy();
    expect(screen.getByLabelText("Password")).toBeTruthy();
    expect(screen.getByRole("button", { name: /sign in/i })).toBeTruthy();
  });
});
