import java.util.*;

/**
 * Calculates total price based on:
 *  - Base movie price
 *  - Seat type multiplier (VIP / Standard / Economy)
 *  - Snack combos
 */
public class PriceCalculator {

    // ── Seat multipliers ────────────────────────────────────────────────────
    public static final Map<String, Double> SEAT_MULTIPLIER = new LinkedHashMap<>();
    static {
        SEAT_MULTIPLIER.put("VIP",      1.5);
        SEAT_MULTIPLIER.put("Standard", 1.0);
        SEAT_MULTIPLIER.put("Economy",  0.75);
    }

    // ── Snack prices ────────────────────────────────────────────────────────
    public static final Map<String, Double> SNACK_PRICES = new LinkedHashMap<>();
    static {
        SNACK_PRICES.put("Popcorn (Large)",     5.00);
        SNACK_PRICES.put("Popcorn (Small)",     3.00);
        SNACK_PRICES.put("Soft Drink",          3.50);
        SNACK_PRICES.put("Nachos",              4.50);
        SNACK_PRICES.put("Hot Dog",             4.00);
        SNACK_PRICES.put("Combo Meal (Popcorn + Drink)", 7.50);
        SNACK_PRICES.put("None",                0.00);
    }

    /**
     * Calculate ticket price.
     * @param basePrice  Movie's base price
     * @param seatType   "VIP", "Standard", or "Economy"
     * @return ticket price before snacks
     */
    public static double ticketPrice(double basePrice, String seatType) {
        double mult = SEAT_MULTIPLIER.getOrDefault(seatType, 1.0);
        return Math.round(basePrice * mult * 100.0) / 100.0;
    }

    /**
     * Total price including selected snacks.
     */
    public static double totalPrice(double basePrice, String seatType, String snack) {
        double ticket = ticketPrice(basePrice, seatType);
        double snackCost = SNACK_PRICES.getOrDefault(snack, 0.0);
        return Math.round((ticket + snackCost) * 100.0) / 100.0;
    }

    /** Get a human-readable price breakdown */
    public static String breakdown(double basePrice, String seatType, String snack) {
        double ticket    = ticketPrice(basePrice, seatType);
        double snackCost = SNACK_PRICES.getOrDefault(snack, 0.0);
        double total     = ticket + snackCost;
        return String.format(
            "Base Price   : $%.2f%n" +
            "Seat (%s) x%.2f : $%.2f%n" +
            "Snack (%s) : $%.2f%n" +
            "─────────────────────%n" +
            "TOTAL        : $%.2f",
            basePrice,
            seatType, SEAT_MULTIPLIER.getOrDefault(seatType, 1.0), ticket,
            snack == null ? "None" : snack, snackCost,
            total
        );
    }
}
