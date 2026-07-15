import javax.swing.*;
import java.awt.*;
import java.io.*;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.Map;

public class ReceiptFrame {

    public static void show(String movieTitle, double ticketTotal,
                            ArrayList<String[]> seats, ArrayList<ConcessionItem> snacks,
                            String paymentMethod, String username) {
        String dateTime   = LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm:ss"));
        double snackTotal = snacks.stream().mapToDouble(ConcessionItem::getTotalCost).sum();
        double grandTotal = ticketTotal + snackTotal;

        StringBuilder sb = new StringBuilder();
        sb.append("==================================================\n");
        sb.append("             CINEMA BOOKING RECEIPT\n");
        sb.append("==================================================\n");
        sb.append(String.format("%-18s %s%n", "Customer:",    username));
        sb.append(String.format("%-18s %s%n", "Movie:",       movieTitle));
        sb.append(String.format("%-18s %s%n", "Date & Time:", dateTime));
        sb.append("--------------------------------------------------\n");
        sb.append(String.format("%-16s %-12s %s%n", "TICKETS", "Type", "Price"));
        sb.append("--------------------------------------------------\n");
        for (String[] seat : seats)
            sb.append(String.format("  %-14s %-12s RM%6s%n", seat[0], seat[1], seat[2]));
        sb.append("--------------------------------------------------\n");
        sb.append(String.format("%-29s RM%6.2f%n", "Ticket Subtotal: ", ticketTotal));

        if (!snacks.isEmpty()) {
            sb.append("--------------------------------------------------\n");
            sb.append(String.format("%-28s %-8s %5s%n", "CONCESSIONS", "Qty", "Price"));
            sb.append("--------------------------------------------------\n");
            LinkedHashMap<String, double[]> itemMap = new LinkedHashMap<>();
            for (ConcessionItem item : snacks) {
                String key = item.getCategory() + " - " + item.getName();
                itemMap.putIfAbsent(key, new double[]{0, item.getPrice()});
                itemMap.get(key)[0] += item.getQuantity();
            }
            for (Map.Entry<String, double[]> entry : itemMap.entrySet()) {
                int qty      = (int) entry.getValue()[0];
                double price = entry.getValue()[1];
                sb.append(String.format("  %-25s  x%-4d  RM%6.2f%n", entry.getKey(), qty, qty * price));
            }
            sb.append("--------------------------------------------------\n");
            sb.append(String.format("%-28s RM%6.2f%n", "Snack Subtotal: ", snackTotal));
        }

        sb.append("==================================================\n");
        String cleanPayment = paymentMethod.replaceAll("[^a-zA-Z0-9/'n ]", "").trim();
        sb.append(String.format("%-28s %s%n",      "Payment:",    cleanPayment));
        sb.append(String.format("%-28s RM%6.2f%n", "GRAND TOTAL:", grandTotal));
        sb.append("==================================================\n");
        sb.append("           Thank you! Enjoy the movie!\n");
        sb.append("==================================================\n");

        JTextArea receiptArea = new JTextArea(sb.toString());
        receiptArea.setEditable(false);
        receiptArea.setFont(new Font(Font.MONOSPACED, Font.PLAIN, 12));
        JOptionPane.showMessageDialog(null, new JScrollPane(receiptArea), "Payment Successful", JOptionPane.PLAIN_MESSAGE);

        if (!snacks.isEmpty())
            saveSnackOrder(movieTitle, dateTime, snacks, snackTotal, username);
    }

    private static void saveSnackOrder(String movieTitle, String dateTime,
                                       ArrayList<ConcessionItem> snacks, double snackTotal,
                                       String username) {
        LinkedHashMap<String, double[]> itemMap = new LinkedHashMap<>();
        for (ConcessionItem item : snacks) {
            itemMap.putIfAbsent(item.getName(), new double[]{0, item.getPrice()});
            itemMap.get(item.getName())[0] += item.getQuantity();
        }

        StringBuilder itemsPart = new StringBuilder();
        boolean first = true;
        for (Map.Entry<String, double[]> entry : itemMap.entrySet()) {
            if (!first) itemsPart.append(";");
            itemsPart.append(entry.getKey()).append("~")
                     .append((int) entry.getValue()[0]).append("~")
                     .append(entry.getValue()[1]);
            first = false;
        }

        String line = username + "|" + movieTitle + "|" + dateTime + "|"
                    + String.format("%.2f", snackTotal) + "|" + itemsPart;

        try (FileWriter fw = new FileWriter("snack_orders.txt", true)) {
            fw.write(line + "\n");
        } catch (IOException e) {
            System.err.println("Error saving snack order: " + e.getMessage());
        }
    }
}
